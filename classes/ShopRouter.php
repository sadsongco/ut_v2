<?php

namespace Router;

include_once('Router.php');

class ShopRouter extends Router
{
    private $renderer;
    private $routes = [
        '/' => [
            'name' => 'Shop',
            'controller' => 'shop/index'
        ],
        '/cart' => [
            'name' => 'Cart',
            'controller' => 'shop/cart'
        ],
        '/checkout' => [
            'name' => 'Checkout',
            'controller' => 'shop/checkout'
        ],
        'item' => [
            'name' => 'Item',
            'controller' => 'shop/item'
        ]
    ];
    function __construct($renderer)
    {
        $this->renderer = $renderer;
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $uri = str_replace('/shop', '', $uri);
        if (array_key_exists($uri, $this->routes)) {
            $this->routes[$uri]['active'] = true;
            $this->nav = ['endpoints'=>[]];
            foreach ($this->routes AS $route) {
                $this->nav['endpoints'][] = $route;
            }
            require base_path('controllers/' . $this->routes[$uri]['controller'] . '.php');
            exit();
        }
        $paths = explode('/', $uri);
        if (array_key_exists($paths[1], $this->routes)) {
            require base_path('controllers/' . $this->routes[$paths[1]]['controller'] . '.php');
            exit();
        }
    }
}