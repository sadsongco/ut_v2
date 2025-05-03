<?php

session_start();

if ((isset($_SESSION['bundles']) && sizeof($_SESSION['bundles'])) || (isset($_SESSION['items']) && sizeof($_SESSION['items'])) > 0) {
    echo '<button class="empty-cart" hx-post="/functions/interface/shop/empty_cart.php" hx-confirm="Delete all items from your cart?">Empty Cart</button>
    <a class="cart-button" href="/shop/cart">View Cart</a>';
}

else echo '<button class="empty-cart" disabled>Cart Is Empty</button>';