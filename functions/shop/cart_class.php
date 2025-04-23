<?php

session_start();

if (isset($_SESSION['items']) && sizeof($_SESSION['items']) > 0) echo '<a class="icon viewCartItems" href="/shop/cart"></a>';

else echo '<a class="icon viewCart" href="/shop/cart"></a>';