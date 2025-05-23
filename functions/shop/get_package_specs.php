<?php

define ("TmpPackageWeight", 180);
define( "TmpPackageSize", 1.2);

function getPackageSpecs($cart_contents)
{
    $all_e_delivery = true;
    $length = $width = $depth = 0;
    $package_weight = 0;
    foreach ($cart_contents['items'] as $item) {
        if ($item['e_delivery'] = 1) continue;
        $all_e_delivery = false;
        $package_weight += $item['weight'] * $item['quantity'] * 1000;
        if ($item['length_mm'] > $length) $length = $item['length_mm'];
        if ($item['width_mm'] > $width) $width = $item['width_mm'];
        $depth += $item['depth_mm'] * $item['quantity'];
    }
    foreach ($cart_contents['bundles'] as $bundle) {
        foreach ($bundle['items'] as $item) {
            if ($item['e_delivery'] = 1) continue;
            $all_e_delivery = false;
            $package_weight += $item['weight'] * $bundle['quantity'] * 1000;
            if ($item['length_mm'] > $length) $length = $item['length_mm'];
            if ($item['width_mm'] > $width) $width = $item['width_mm'];
            $depth += $item['depth_mm'] * $bundle['quantity'];
        }
    }

    if ($all_e_delivery) {
        return [
            "e_delivery"=>true
        ];
    }

    return [
        "weight"=>$package_weight + TmpPackageWeight,
        "length"=>$length * TmpPackageSize,
        "width"=>$width * TmpPackageSize,
        "depth"=>$depth * TmpPackageSize
    ];
}