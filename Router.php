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

    /**
     * @var array
     */
    private $namedRoutes = [];

    public function __construct ($namespace, $url)
    {
        $this->namespace = $namespace;
        $this->url = $url;
    }

    public function get ($path, $callable, $name = null)
    {
        return $this->addRoute(['HEAD', 'GET', 'OPTIONS'], $path, $callable, $name);
    }

    public function post ($path, $callable, $name = null)
    {
        return $this->addRoute('POST', $path, $callable, $name);
    }

    public function patch ($path, $callable, $name = null)
    {
        return $this->addRoute('PATCH', $path, $callable, $name);
    }

    public function put ($path, $callable, $name = null)
    {
        return $this->addRoute('PUT', $path, $callable, $name);
    }

    public function delete ($path, $callable, $name = null)
    {
        return $this->addRoute('DELETE', $path, $callable, $name);
    }

    public function options ($path, $callable, $name = null)
    {
        return $this->addRoute('OPTIONS', $path, $callable, $name);
    }

    private function addRoute ($methods, $path, $callable, $name = null)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }
        $route = new Route($path, $callable);
        $route->setRouter($this);

        if(is_string($callable) && $name === null){
            $name = $callable;
        }

        if($name){
            $this->namedRoutes[$name] = $route;
        }

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

    public function getUrl ($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException('No route matches this name');
        }
        return $this->namedRoutes[$name]->getUrl($params);
    }

    /**
     * @return mixed
     */
    public function getNamespace ()
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getRoutes (): array
    {
        return $this->routes;
    }
}