<?php

namespace Router;

class Router
{
    private $routes = [
        '/' => 'index.php',
        '/blog' => 'blog.php',
    ];
    private $renderer;

    function __construct($renderer)
    {
        $this->renderer = $renderer;
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        if (array_key_exists($uri, $this->routes)) {
            require base_path('controllers/' . $this->routes[$uri]);
        } else {
            $this->abort();
        }
    }

    function abort($code = 404) 
    {
        http_response_code($code);
        require base_path('controllers/abort.php');
        die();
    }


}