<?php

namespace Lumille\Routing;

class Route
{
    private $path;
    private $callable;

    /**
     * @var Router
     */
    private $router;
    private $matches = [];
    private $params = [];

    public function __construct ($path, $callable)
    {
        $this->path = trim($path, '/');  // On retire les / inutils
        $this->callable = $callable;
    }

    /**
     * Permettra de capturer l'url avec les paramètre
     * get('/posts/:slug-:id') par exemple
     **/
    public function match ($url)
    {
        $url = trim($url, '/');
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

        $this->matches = $parameters;  // On sauvegarde les paramètre dans l'instance pour plus tard
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

        return \call_user_func_array($callable, $args);
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
        return $path;
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
     */
    public function setRouter ($router): void
    {
        $this->router = $router;
    }
}