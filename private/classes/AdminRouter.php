<?php

namespace Router;

include('../classes/Router.php');

class AdminRouter extends Router
{
    private $renderer;
    private $routes = [
        '' => [
            'name' => 'Admin Index',
            'controller' => 'index'
        ],
        'stock' => [
            'name' => 'Stock Management',
            'controller' => 'stock/index'
        ]
    ];
    function __construct($renderer)
    {
        $this->renderer = $renderer;
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $paths = explode('/', $uri);
        array_shift($paths);
        $base_path = array_shift($paths);
        $path = array_shift($paths);
        if (array_key_exists($path, $this->routes)) {
            $this->routes[$path]['active'] = true;
            $this->nav = ['endpoints'=>[]];
            foreach ($this->routes AS $route) {
                $this->nav['endpoints'][] = $route;
            }
            require base_path('private/controllers/' . $this->routes[$path]['controller'] . '.php');
        } else {
            $this->nav = [];
            foreach ($this->routes AS $route) {
                $this->nav[] = $route;
            }
            $this->abort();
        }
    }
}