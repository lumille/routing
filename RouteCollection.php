<?php


namespace Lumille\Routing;


class RouteCollection
{
    /**
     * @var RouteCollection
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var array
     */
    protected $namedRoutes = [];

    /**
     * @return RouteCollection
     */
    public static function getInstance ()
    {
        if (static::$instance === null) {
            static::$instance = new RouteCollection();
        }

        return static::$instance;
    }

    /**
     * @param $methods
     * @param $route
     * @param $name
     */
    public function setRoute ($methods, $route, $name)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        if($name){
            $this->namedRoutes[$name] = $route;
        }

        foreach ($methods as $method) {
            $method = \mb_convert_case($method, \MB_CASE_UPPER);
            if (!isset($this->routes[$method])) {
                $this->routes[$method] = [];
            }
            $this->routes[$method][] = $route;
        }
    }

    /**
     * @return array
     */
    public function getAllRoutes ()
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getNamedRoutes ()
    {
        return $this->namedRoutes;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getRouteByName ($name)
    {
        return $this->namedRoutes[$name] ?? null;
    }
}