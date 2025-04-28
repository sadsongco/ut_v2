<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function calculateCartSubtotal($cart_items)
{
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $_SESSION['subtotal'] = $subtotal;
    return number_format($subtotal, 2);
}