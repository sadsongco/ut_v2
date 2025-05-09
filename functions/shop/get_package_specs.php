<?php


function getPackageSpecs($cart_contents)
{
    $length = $width = $depth = 0;
    $package_weight = 0;
    foreach ($cart_contents['items'] as $item) {
        $package_weight += $item['weight'] * $item['quantity'] * 1000;
        if ($item['length_mm'] > $length) $length = $item['length_mm'];
        if ($item['width_mm'] > $width) $width = $item['width_mm'];
        $depth += $item['depth_mm'] * $item['quantity'];
    }
    foreach ($cart_contents['bundles'] as $bundle) {
        foreach ($bundle['items'] as $item) {
            $package_weight += $item['weight'] * $bundle['quantity'] * 1000;
            if ($item['length_mm'] > $length) $length = $item['length_mm'];
            if ($item['width_mm'] > $width) $width = $item['width_mm'];
            $depth += $item['depth_mm'] * $bundle['quantity'];
        }
    }
    return [
        "weight"=>$package_weight,
        "length"=>$length,
        "width"=>$width,
        "depth"=>$depth
    ];
}