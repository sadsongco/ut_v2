<?php

session_start();

$_SESSION['items'] = [];

session_destroy();

echo "Your cart is empty";