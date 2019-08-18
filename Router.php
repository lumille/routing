<?php

namespace Lumille\Routing;

class Router
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $url; // Contiendra l'URL sur laquelle on souhaite se rendre

    /**
     * @var array
     */
    private $routes = []; // Contiendra la liste des routes

    public function __construct ($namespace, $url)
    {
        $this->namespace = $namespace;
        $this->url = $url;
    }

    public function get ($path, $callable)
    {
        return $this->addRoute(['HEAD', 'GET'], $path, $callable);
    }

    private function addRoute ($methods, $path, $callable)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }
        $route = new Route($path, $callable);
        $route->setRouter($this);

        foreach ($methods as $method) {
            $this->routes[\strtoupper($method)][] = $route;
        }

        return $route;
    }

    public function run ()
    {
        if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD does not exist');
        }
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->url)) {
                return $route->call();
            }
        }
        throw new RouterException('No matching routes');
    }

    /**
     * @return mixed
     */
    public function getNamespace ()
    {
        return $this->namespace;
    }
}