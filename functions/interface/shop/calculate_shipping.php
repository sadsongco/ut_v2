<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . "/../../functions.php");
include_once(base_path("functions/shop/get_cart_items.php"));
include_once(base_path("classes/Database.php"));
use Database\Database;

class PackagingCosts {
    public const LABOUR = 0.5;
    public const PACKAGING = 1;
}

function calculateShipping($db, $zone, $method) {
    try {
        $query = "SELECT package_id, name
        FROM Packages
        WHERE max_length_mm >= ?
        AND max_width_mm >= ?
        AND max_depth_mm >= ?
        AND max_weight_g >= ?
        AND zone = ?";
        $package_zone = $zone == "UK" ? "UK" : "ROW";
        $params = [
            $_SESSION['package_specs']['length'],
            $_SESSION['package_specs']['width'],
            $_SESSION['package_specs']['depth'],
            $_SESSION['package_specs']['weight'],
            $package_zone
        ];
        $package_specs = $db->query($query, $params)->fetch();
        $_SESSION['package_specs']['package_id'] = $package_specs['package_id'];
        $_SESSION['package_specs']['package_name'] = $package_specs['name'];
        $query = "SELECT
            shipping_price
        FROM Shipping_prices
        WHERE package_id = ?
        AND shipping_method_id = ?
        AND rm_zone = ?
        AND min_weight_g <= ?
        AND max_weight_g >= ?";
        $params = [
            $package_specs['package_id'],
            $method['shipping_method_id'],
            $zone,
            $_SESSION['package_specs']['weight'],
            $_SESSION['package_specs']['weight']
        ];
        $shipping_price = $db->query($query, $params)->fetch();
        if (!$shipping_price) throw new Exception("No applicable shipping price found");
        return $shipping_price['shipping_price'] + $_SESSION['package_specs']['package_price'] + PackagingCosts::LABOUR;
    } catch (Exception $e) {
        throw new Exception($e);
    }
    return 0;
}

if (isset($_POST['update'])) {
    $db = new Database('orders');
    $shipping_method = $db->query("SELECT * FROM Shipping_methods WHERE shipping_method_id = ?", [$_SESSION['shipping_method']['shipping_method_id']])->fetch();
    $shipping = 0;
    if (!isset($_SESSION['package_specs']['e_delivery'])) {
        $shipping = calculateShipping($db, $_SESSION['rm_zone'], $shipping_method);
        $_SESSION['shipping'] = round($shipping, 2);
    }

    header("HX-Trigger: shippingUpdated");
    echo number_format($shipping, 2);
}
