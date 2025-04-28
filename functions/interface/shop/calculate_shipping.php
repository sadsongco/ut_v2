<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . "/../../functions.php");
include_once(base_path("functions/shop/get_cart_items.php"));
include_once(base_path("functions/shop/get_shipping_methods.php"));
include_once(base_path("classes/Database.php"));
use Database\Database;

class PackagingCosts {
    public const LABOUR = 0.5;
    public const PACKAGING = 1;
}

function calculateShipping($cart_items, $db, $zone, $method) {
    $length = $width = $depth = 0;
    $package_weight = 0;
    foreach ($cart_items as $item) {
        $package_weight += $item['weight'] * $item['quantity'] * 1000;
        if ($item['length_mm'] > $length) $length = $item['length_mm'];
        if ($item['width_mm'] > $width) $width = $item['width_mm'];
        $depth += $item['depth_mm'] * $item['quantity'];
    }
    try {
        $query = "SELECT package_id, name
        FROM Packages
        WHERE max_length_mm >= ?
        AND max_width_mm >= ?
        AND max_depth_mm >= ?
        AND max_weight_g >= ?
        AND zone = ?";
        $params = [$length, $width, $depth, $package_weight, $zone];
        $package_specs = $db->query($query, $params)->fetch();
        $query = "SELECT shipping_price FROM Shipping_prices WHERE package_id = ? AND shipping_method_id = ?";
        $params = [$package_specs['package_id'], $method['shipping_method_id']];
        $shipping_price = $db->query($query, $params)->fetch();
        return $shipping_price['shipping_price'] + PackagingCosts::LABOUR + PackagingCosts::PACKAGING;
    } catch (Exception $e) {
        throw new Exception($e);
    }
    return 0;
}

if (isset($_POST['update'])) {

    $db = new Database('orders');

    $shipping_method = $db->query("SELECT * FROM Shipping_methods WHERE shipping_method_id = ?", [$_SESSION['shipping_method']])->fetch();
    
    $cart_items = getCartItems($_SESSION['items'], $db);
    $shipping = calculateShipping($cart_items, $db, $_SESSION['zone'], $shipping_method);
    $_SESSION['shipping'] = round($shipping, 2);

    header("HX-Trigger: shippingUpdated");
    echo number_format($shipping, 2);
}
