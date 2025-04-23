<?php

session_start();

if (isset($_SESSION['items']) && sizeof($_SESSION['items']) > 0) echo '<button class="emptyCart" hx-post="/functions/interface/shop/empty_cart.php" hx-confirm="Delete all items from your cart?">Empty Cart</button>';

else echo '<button class="emptyCart" disabled>Cart Is Empty</button>';