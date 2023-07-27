<?php

class Router
{
    private static $routes = array();

    public static function get($route, $handler)
    {
        self::addRoute('GET', $route, $handler);
    }

    public static function post($route, $handler)
    {
        self::addRoute('POST', $route, $handler);
    }

    private static function addRoute($method, $route, $handler)
    {
        $route = rtrim($route, '/');
        self::$routes[] = array(
            'method' => $method,
            'route' => $route,
            'handler' => $handler,
        );
    }

    public static function dispatch()
    {
        $requestUri = self::currentUri();
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {
            $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?<$1>[^/]+)', str_replace('/', '\/', $route['route'])) . '$#';

            if (preg_match($pattern, $requestUri, $matches) && strtoupper($requestMethod) === $route['method']) {
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                call_user_func_array($route['handler'], $params);
                return;
            }
        }
        echo "404 Not Found";
    }

    private static function currentUri()
    {
        $currentUri = strtok($_SERVER['REQUEST_URI'], '?');
        $currentUri = rtrim($currentUri, '/');
        return $currentUri;
    }
}
