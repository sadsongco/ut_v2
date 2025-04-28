<?php


function getPackageSpecs($cart_items)
{
    $length = $width = $depth = 0;
    $package_weight = 0;
    foreach ($cart_items as $item) {
        $package_weight += $item['weight'] * $item['quantity'] * 1000;
        if ($item['length_mm'] > $length) $length = $item['length_mm'];
        if ($item['width_mm'] > $width) $width = $item['width_mm'];
        $depth += $item['depth_mm'] * $item['quantity'];
    }
    return [
        "weight"=>$package_weight,
        "length"=>$length,
        "width"=>$width,
        "depth"=>$depth
    ];
}