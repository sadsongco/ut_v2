<?php

function calculateCartSubtotal($cart_items)
{
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'];
    }
    return number_format($subtotal, 2);
}