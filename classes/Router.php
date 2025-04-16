<?php

namespace Router;

class Router
{
    private $routes = [
        '/' => [
            'path' => '/',
            'controller' => 'index',
            'name' => 'Home',
        ],
        '/blog' => [
            'path' => '/blog',
            'controller' => 'blog',
            'name' => 'Blog',
        ],
    ];
    private $renderer;
    public $nav = [];

    function __construct($renderer)
    {
        $this->renderer = $renderer;
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        if (array_key_exists($uri, $this->routes)) {
            $this->routes[$uri]['active'] = true;
            $this->nav = ['endpoints'=>[]];
            foreach ($this->routes AS $route) {
                $this->nav['endpoints'][] = $route;
            }
            require base_path('controllers/' . $this->routes[$uri]['controller'] . '.php');
        } else {
            $this->nav = [];
            foreach ($this->routes AS $route) {
                $this->nav[] = $route;
            }
            $this->abort();
        }
    }

    function abort($code = 404) 
    {
        http_response_code($code);
        require base_path('controllers/abort.php');
        die();
    }

    function get_nav()
    {
        return $this->nav;
    }
}