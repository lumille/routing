<?php

namespace Lumille\Routing;

use Symfony\Component\HttpFoundation\Request;

class Router
{

    /**
     * @var array
     */
    private $namedRoutes = [];

    /**
     * @var string
     */
    private $prefix;
    /**
     * @var Request
     */
    private $request;

    public function __construct (Request $request)
    {
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
        $router = new Router($this->request);
        $prefix = rtrim($prefix, '/');
        $prefix = $this->prefix . $prefix;
        $router->setPrefix($prefix);
        return \call_user_func_array($callable, [$router]);
    }

    private function addRoute ($methods, $path, $callable, $name = null)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        $path = $this->rtrim($path);
        $path = $this->addPrefix($path);

        $_route = RouteCollection::getInstance()->getRouteByPath($path);

        if ($_route) {
            $_route->setAcceptMethods($methods);
            RouteCollection::getInstance()->setNamedRoutes($_route, $path);
            return;
        }

        $route = new Route($path, $callable);
        $route->setRouter($this);
        $route->setAcceptMethods($methods);

        if (is_string($callable) && $name === null) {
            $name = $callable;
        }

        RouteCollection::getInstance()->setNamedRoutes($route, $name);

        RouteCollection::getInstance()
            ->setRoute($route, $path);

        return $route;
    }

    public function run ()
    {
        $uri = $this->request->getPathInfo();
        $uri = $this->rtrim($uri);
        $routes = RouteCollection::getInstance()
            ->getAllRoutes();

        foreach ($routes as $route) {
            if ($route->match($uri)) {
                if ($route->checkAcceptMethods($this->request->getMethod())) {
                    return $route->call();
                }

                throw new MethodNotAcceptedException("Method not accepted");
            }
        }

        throw new UrlNotFoundException('No matching routes');
    }


    public  function getUrl ($name, $params = [])
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
    public
    function getPrefix ()
    {
        return $this->prefix;
    }

    public
    function addPrefix ($path)
    {
        if ($this->prefix) {
            $path = $this->prefix . $path;
        }

        return $path;
    }

    /**
     * @return Request
     */
    public
    function getRequest (): Request
    {
        return $this->request;
    }

    /**
     * @param string $prefix
     * @return Router
     */
    public
    function setPrefix (string $prefix): Router
    {
        $prefix = rtrim($prefix);
        $this->prefix .= $prefix;
        return $this;
    }

    private function rtrim ($path)
    {
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}