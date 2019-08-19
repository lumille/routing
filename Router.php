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
    private $request;

    /**
     * @var array
     */
    private $namedRoutes = [];

    /**
     * @var string
     */
    private $prefix;

    public function __construct ($namespace, $request)
    {
        $this->namespace = $namespace;
        $this->request = $request;
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

    public function prefix ($prefix, $callable)
    {
        $router = new Router($this->getNamespace(), $this->getRequest());
        $router->setPrefix($prefix);
        \call_user_func_array($callable, [$router]);
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

        RouteCollection::getInstance()
            ->setRoute($methods, $route, $name);

        return $route;
    }

    public function run ()
    {
        $routes = RouteCollection::getInstance()
            ->getAllRoutes();
        if (!isset($routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD does not exist');
        }
        foreach ($routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->request)) {
                return $route->call();
            }
        }
        throw new RouterException('No matching routes');
    }

    public function getUrl ($name, $params = [])
    {
        $namedRoutes = RouteCollection::getInstance()
            ->getNamedRoutes();
        if (!isset($namedRoutes[$name])) {
            throw new RouterException('No route matches this name');
        }
        return $namedRoutes[$name]->getUrl($params);
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

    /**
     * @return mixed
     */
    public function getPrefix ()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getRequest (): string
    {
        return $this->request;
    }

    /**
     * @param string $prefix
     * @return Router
     */
    public function setPrefix (string $prefix): Router
    {
        $this->prefix = $prefix;
        return $this;
    }
}