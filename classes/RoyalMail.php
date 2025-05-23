<?php

namespace RoyalMail;

use DateTime;
use PDOException;

include_once(base_path("functions/shop/get_cart_contents.php"));
include_once(base_path("functions/shop/get_package_specs.php"));
include_once(base_path("functions/shop/get_shipping_methods.php"));
include_once(base_path("functions/shop/calculate_cart_subtotal.php"));
include_once(base_path("functions/interface/shop/calculate_shipping.php"));

function jsFormatDate($date) {
    $dateObj = new DateTime($date);
    return date_format($dateObj, 'Y-m-d\TH:i:s\Z');
}


class RoyalMail {

    protected $db;
    protected $order_id;
    protected $order_data;
    protected $rm_order;
    protected $order_outcomes;
    function __construct($order_id, $db)
    {
        $this->db = $db;
        $this->order_id = $order_id;
        $this->getOrderData();
    }

    private function getOrderData()
    {
        try {
            $query = "SELECT
                New_Orders.order_id,
                New_Orders.sumup_id,
                TRIM(New_Orders.shipping_method) AS shipping_method,
                New_Orders.shipping,
                New_Orders.subtotal,
                New_Orders.vat,
                New_Orders.total,
                New_Orders.order_date,
                New_Orders.package_specs,
                Customers.name,
                Customers.address_1,
                Customers.address_2,
                Customers.city,
                Customers.postcode,
                Customers.country,
                Customers.email
            FROM New_Orders
            LEFT JOIN Customers ON New_Orders.customer_id = Customers.customer_id
            WHERE `order_id` = ?
            ";
            $params = [$this->order_id];
            $this->order_data = $this->db->query($query, $params)->fetch();
            $this->order_data['package_specs'] = json_decode($this->order_data['package_specs'], true);
            $this->getCountryCode();
            $this->getShippingMethod();
            $this->getItems();
        } catch (PDOException $e) {
            echo $e->getMessage(); 
        }
    }

    private function getCountryCode ()
    {
        $query = "SELECT country_code FROM Countries WHERE country_id = ?";
        $params = [$this->order_data['country']];
        $result = $this->db->query($query, $params)->fetch();
        $this->order_data['country_code'] = $result['country_code'];
    }

    private function getShippingMethod()
    {
        $query = "SELECT service_name, service_code FROM Shipping_methods WHERE shipping_method_id = ?";
        $params = [$this->order_data['shipping_method']];
        $this->order_data['rm_shipping_method'] = $this->db->query($query, $params)->fetch();
    }

    private function getItems()
    {
        $query = "SELECT
            Items.name,
            Items.price,
            Items.weight,
            Items.customs_description,
            Items.customs_code,
            New_Order_items.amount
            FROM New_Order_items
            JOIN Items ON New_Order_items.item_id = Items.item_id
            WHERE New_Order_items.order_id = ?
            ";
        $params = [$this->order_id];
        $items = $this->db->query($query, $params)->fetchAll();
        $query = "SELECT
                Items.name,
                Items.price,
                Items.weight,
                Items.customs_description,
                Items.customs_code,
                Order_bundles.amount
            FROM Order_bundles
            JOIN Bundle_items ON Bundle_items.bundle_id = Order_bundles.bundle_id
            JOIN Items ON Items.item_id = Bundle_items.item_id
            WHERE Order_bundles.order_id = ?
        ";
        $params = [$this->order_id];
        $bundle_items = $this->db->query($query, $params)->fetchAll();
        $this->order_data['items'] = array_merge($items, $bundle_items);
    }

    public function displayOrderData()
    {
        return $this->order_data;
    }

    public function displayRMOrder()
    {
        return $this->rm_order;
    }

    public function createRMOrder()
    {
        $this->order_data['order_date'] = jsFormatDate($this->order_data['order_date']);
        if (!$this->order_data['rm_shipping_method']['service_code']) return false;
        $order_items = [];
        foreach($this->order_data['items'] as $item) {
            $order_items[] = $this->createRMItem($item);
        }
        $this->order_data['items'] = $order_items;
        $this->rm_order = [
            "orderReference"=>$this->order_data['order_id'],
            "recipient"=>[
                "address"=>[
                "fullName"=>$this->order_data['name'],
                "companyName"=>"",
                "addressLine1"=>$this->order_data['address_1'],
                "addressLine2"=>$this->order_data['address_2'] ?? "",
                "addressLine3"=>"",
                "city"=>$this->order_data['city'],
                "county"=>"",
                "postcode"=>$this->order_data['postcode'],
                "countryCode"=>$this->order_data['country_code']
                ],
                "phoneNumber"=>"",
                "emailAddress"=>$this->order_data['email']
            ],
            "sender"=>[
                "tradingName"=>"Unbelievable Truth",
                "phoneNumber"=>"07787 782550",
                "emailAddress"=>"info@unbelievabletruth.co.uk",
                "addressBookReference"=>"001"
            ],
            "packages"=>[
                [
                    "weightInGrams"=>(string)$this->order_data['package_specs']['weight'],
                    "packageFormatIdentifier"=>strtolower($this->order_data['package_specs']['package_name']),
                    "contents"=>$order_items
                ],
            ],
            "orderDate"=>$this->order_data['order_date'],
            "plannedDespatchDate"=>"",
            "subtotal"=>(float)$this->order_data['subtotal'],
            "shippingCostCharged"=>(float)$this->order_data['shipping'],
            "otherCosts"=>"0",
            "total"=>(float)$this->order_data['total'],
            "currencyCode"=>"GBP",
            "postageDetails"=>[
                "sendNotificationsTo"=>"recipient",
                "serviceCode"=>$this->order_data['rm_shipping_method']['service_code'],
                "serviceRegisterCode"=>"",
                "receiveEmailNotification"=>false,
                "receiveSmsNotification"=>false,
                "guaranteedSaturdayDelivery"=>false,
                "requestSignatureUponDelivery"=>false,
                "isLocalCollect"=>false
            ],
            "tags"=>[
                [
                "key"=>"string",
                "value"=>"string"
                ]
            ],
            "label"=>[
                "includeLabelInResponse"=>false,
                "includeCN"=>false,
                "includeReturnsLabel"=>false
            ],
        ];
    }

    private function createRMItem($item) {
        $rm_item = [
            "name"=>$item['name'],
            "quantity"=>$item['amount'],
            "unitValue"=>$item['price'],
            "unitWeightInGrams"=>(int)$item['weight']*1000,
            "customsDescription"=>$item['customs_description'],
            "extendedCustomsDescription"=>$item['name'],
            "customsCode"=>$item['customs_code'],
            "originCountryCode"=>"GBR",
            "customsDeclarationCategory"=>"SaleOfGoods",
            "requiresExportLicence"=>false,
            "stockLocation"=>"GB"
        ];
        return $rm_item;
    }

    public function submitRMOrder()
    {
        $data = [
            "items"=>[
                $this->rm_order
            ]
        ];

        if (sizeof($data['items']) == 0) {
            echo "No orders to submit.<br>";
            exit();
        }

        $path = RM_BASE_URL."/orders";
        // $path = RM_BASE_URL."/version";
        $headers = [
            "Authorization: " . RM_API_KEY,
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $rm_order);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseObj = json_decode($response);

        $order_outcomes = [];

        if (isset($responseObj->createdOrders)) {
            foreach($responseObj->createdOrders as $successful_order) {
                $query = "UPDATE `New_Orders`
                SET 
                `rm_order_identifier` = ?,
                `rm_created` = ?
                WHERE `order_id` = ?";
                $params = [
                    (int)$successful_order->orderIdentifier,
                    $successful_order->createdOn,
                    (int)$successful_order->orderReference,
                ];
                $stmt = $this->db->query($query, $params);
                if ($this->db->rowCount($stmt) == 0) {
                    $order_outcomes[] = "FAILED to update database for " . $successful_order->orderReference . " : " . $this->db->error;
                    continue;
                }
                $order_outcomes[] = "Order id " . $successful_order->orderReference . " submitted to Royal Mail";
            }
        }

        if (isset($responseObj->failedOrders)) {
            foreach($responseObj->failedOrders as $failed_order) {
                $order_outcomes[] = "FAILED TO CREATE ORDER: " . $failed_order->errors[0]->errorMessage;
            };
        }
        $this->order_outcomes = $order_outcomes;
        return $this;
    }

    public function getOrderOutcomes() {
        return $this->order_outcomes;
    }

};
