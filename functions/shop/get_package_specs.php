<?php

class PACKAGE_FORMATS {
    public const KITE_BAG = [
        "name" => "Kite Bag",
        "length_mm" => 225,
        "height_mm" => 318,
        "depth_mm" => 25,
        "weight_g" => 7,
        "unit_price_p" => 6
    ];
    public const CD_JIFFY = [
        "name" => "CD Jiffy",
        "length_mm" => 200,
        "height_mm" => 173,
        "depth_mm" => 25,
        "weight_g" => 8,
        "unit_price_p" => 19          
    ];
    public const LP_MAILER = [
        "name" => "LP Mailer",
        "length_mm" => 350,
        "height_mm" => 350,
        "depth_mm" => 50,
        "weight_g" => 195,
        "unit_price_p" => 83
    ];
    public const DEFAULT = [
        "name" => "Default",
        "length_mm" => 350,
        "height_mm" => 350,
        "depth_mm" => 200,
        "weight_g" => 250,
        "unit_price_p" => 0
    ];
}

define("ITEM_PACKAGES", [
    "CD_1"=>PACKAGE_FORMATS::CD_JIFFY,
    "CD_2"=>PACKAGE_FORMATS::CD_JIFFY,
    "LP_1"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_2"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_3"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_4"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_5"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_6"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_7"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_8"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_9"=>PACKAGE_FORMATS::LP_MAILER,
    "SHIRT_1"=>PACKAGE_FORMATS::KITE_BAG,
    "CD_1-SHIRT_1"=>PACKAGE_FORMATS::KITE_BAG,
    "CD_2-SHIRT_1"=>PACKAGE_FORMATS::KITE_BAG,
    "LP_1-SHIRT_1"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_2-SHIRT_1"=>PACKAGE_FORMATS::LP_MAILER,
    "LP_3-SHIRT_1"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_1-LP_1"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_1-LP_2"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_1-LP_3"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_2-LP_1"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_2-LP_2"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_2-LP_3"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_3-LP_1"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_3-LP_2"=>PACKAGE_FORMATS::LP_MAILER,
    "CD_3-LP_3"=>PACKAGE_FORMATS::LP_MAILER,
    "DEFAULT"=>PACKAGE_FORMATS::DEFAULT
]);

function getPackageSpecs($cart_contents)
{
    $all_e_delivery = true;
    $length = $width = $depth = 0;
    $items_weight = 0;
    $packaging_classifications = [];
    foreach ($cart_contents['items'] as $item) {
        if ($item['e_delivery'] == 1) continue;
        addPackagingClassification($item, $packaging_classifications);
        $all_e_delivery = false;
        $items_weight += getItemWeight($item);
        if ($item['length_mm'] > $length) $length = $item['length_mm'];
        if ($item['length_mm'] > $width) $width = $item['length_mm'];
        $depth += $item['depth_mm'] * $item['quantity'];
    }
    foreach ($cart_contents['bundles'] as $bundle) {
        foreach ($bundle['items'] as $item) {
            if ($item['e_delivery'] == 1) continue;
            addPackagingClassification($item, $packaging_classifications);
            $all_e_delivery = false;
            $items_weight += getItemWeight($item);
            if ($item['length_mm'] > $length) $length = $item['length_mm'];
            if ($item['length_mm'] > $width) $width = $item['length_mm'];
            $depth += $item['depth_mm'] * $bundle['quantity'];
        }
    }
    ksort($packaging_classifications);
    $arr = [];
    foreach ($packaging_classifications as $key=>$value) {
        $arr[] = $key . "_" . $value;
    }
    $packaging_classification = implode("-", $arr);
    if (!isset(ITEM_PACKAGES[$packaging_classification])) {
        $packaging_classification = "DEFAULT";
    }
    $package_specs = ITEM_PACKAGES[$packaging_classification];
    $total_weight = $items_weight + $package_specs["weight_g"];

    if ($all_e_delivery) {
        return [
            "e_delivery"=>true
        ];
    }

    return [
        "weight"=>$total_weight,
        "length"=>$package_specs["length_mm"],
        "width"=>$package_specs["length_mm"],
        "depth"=>$package_specs["depth_mm"],
        "package_format"=>$package_specs["name"],
        "package_price"=>$package_specs["unit_price_p"] / 100
    ];
}

function addPackagingClassification ($item, &$packaging_classifications)
{
    if (!isset($packaging_classifications[$item['packaging_classification']])) {
        $packaging_classifications[$item['packaging_classification']] = $item['quantity'];
    } else {
        $packaging_classifications[$item['packaging_classification']] += $item['quantity'];
    }
}

function getItemWeight ($item) {
    if (isset($item['option_id']) && $item['option_id']) {
        return $item['option_weight'] * $item['quantity']; //stored in grams. annoyingly
    }
    return $item['weight'] * $item['quantity'] * 1000;
}