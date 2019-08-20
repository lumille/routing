<?php

namespace Lumille\Routing;

class Route
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $matches = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $prefix;

    public function __construct ($path, $callable)
    {
        $this->setPath($path);
        $this->setCallable($callable);
    }

    /**
     * Permettra de capturer l'url avec les paramètre
     * get('/posts/:slug-:id') par exemple
     **/
    public function match ($url)
    {
        $url = rtrim($url, '/');
        $this->updatePathWithPrefix();

        preg_match_all('#{([\w]+)}#i', $this->path, $params);
        $path = preg_replace_callback('#{([\w]+)}#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }

        $parameters = [];
        if ($params && is_array($params)) {
            array_shift($matches);
            \array_shift($params);
            $params = current($params);
            foreach ($params as $k => $param) {
                if (isset($matches[$k])) {
                    $parameters[$param] = $matches[$k];
                }
            }
        }

        $this->matches = $parameters;
        return true;
    }

    public function call ()
    {
        $callable = $this->callable;
        if (!\is_callable($callable)) {
            @list($controller, $method) = explode('::', $this->callable);
            $controller = $this->router->getNamespace() . $controller;
            $controller = new $controller;
            $callable = [$controller, $method];
        }

        $args = $this->getParameters($callable);

        return [$callable, $args];
    }

    /**
     * @param string $path
     * @return Route
     */
    public function setPath (string $path): Route
    {
        $this->path = rtrim($path, '/');

        return $this;
    }

    /**
     * @param string|callable $callable
     * @return Route
     */
    public function setCallable ($callable): Route
    {

        $this->callable = $callable;

        return $this;
    }

    /**
     * @param string $prefix
     * @return Route
     */
    public function setPrefix (string $prefix): Route
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function updatePathWithPrefix ()
    {
        if ($this->prefix || $this->router->getPrefix()) {
            $prefix = current(array_filter([$this->prefix, $this->router->getPrefix()]));
            $this->path = rtrim(rtrim($prefix, '/') . $this->path, '/');
        }
    }

    private function paramMatch ($match)
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }
        return '([^/]+)';
    }

    private function getParameters ($callable)
    {
        $args = [];

        if (!\is_array($callable) && \is_callable($callable)) {
            $x = new \ReflectionFunction($this->callable);
        } else {
            $x = new \ReflectionMethod($callable[0], $callable[1]);
        }
        $params = $x->getParameters();

        foreach ($params as $param) {
            $name = $param->getName();
            if (isset($this->matches[$name])) {
                $args[$name] = $this->matches[$name];
            }
        }

        return $args;
    }

    public function with ($param, $regex)
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);
        return $this; // On retourne tjrs l'objet pour enchainer les arguments
    }

    public function getUrl ($params)
    {
        $path = $this->path;

        foreach ($params as $k => $v) {
            $path = str_replace("{$k}", $v, $path);
        }

        return $path === "" ? "/" : $path;
    }

    /**
     * @return mixed
     */
    public function getRouter ()
    {
        return $this->router;
    }

    /**
     * @param mixed $router
     * @return Router
     */
    public function setRouter ($router)
    {
        $this->router = $router;
    }
}