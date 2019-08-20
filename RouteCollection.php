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
    public function setRoute ($route, $path = null)
    {
        if($path){
            $this->routes[$path] = $route;
        }
        $this->routes[] = $route;
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

    public function getRouteByPath ($path)
    {

        return $this->routes[$path] ?? null;
    }

    /**
     * @param Route
     * @param string $name
     */
    public function setNamedRoutes (Route $route, $name = null)
    {
        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
    }
}