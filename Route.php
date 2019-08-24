<?php

namespace Lumille\Routing;

class Route
{
    /**
     * @var array
     */
    private $paths = [];

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

    private $acceptMethods = [];

    /**
     * @var string
     */
    private $prefix;

    public function __construct($path, $callable)
    {
        $this->setPath($path);
        $this->setCallable($callable);
    }

    public function checkAcceptMethods($method)
    {
        if (!\in_array($method, $this->getAcceptMethods())) {
            throw new MethodNotAcceptedException("Method is not accepted");
        }

        return true;
    }

    /**
     * Permettra de capturer l'url avec les paramètre
     * get('/posts/:slug-:id') par exemple
     **/
    public function match($url)
    {
        $url = rtrim($url, '/');
        $response = false;

        foreach ($this->paths as $path) {
            preg_match_all('#{([\w]+)}#i', $path, $params);
            $path = preg_replace_callback('#{([\w]+)}#', [$this, 'paramMatch'], $path);
            $regex = "#^$path$#i";
            if (!preg_match($regex, $url, $matches)) {
                continue;
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
            $response = true;
            break;
        }

        return $response;
    }

    public function call()
    {
        return [$this->callable, $this->matches];
    }

    /**
     * @param string $path
     * @return Route
     */
    public function setPath(string $path): Route
    {
        $path = rtrim($path, '/');
        preg_match_all("#{\w+\?}#", $path, $matches);
        if (!empty($matches)) {
            $matches = array_shift($matches);
            $newPath = preg_replace("#\?#", "", $path);
            $this->paths[] = $newPath;
            $matches = array_reverse($matches);
            foreach ($matches as $m) {
                $newPath = rtrim(str_replace($m, "", $path), '/');
                $this->paths[] = $newPath;
                $path = $newPath;
             }
        } else {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * @param string|callable $callable
     * @return Route
     */
    public function setCallable($callable): Route
    {

        $this->callable = $callable;

        return $this;
    }

    /**
     * @param string $prefix
     * @return Route
     */
    public function setPrefix(string $prefix): Route
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return array
     */
    public function getAcceptMethods(): array
    {
        return $this->acceptMethods;
    }

    /**
     * @param array $acceptMethods
     */
    public function setAcceptMethods(array $acceptMethods)
    {
        foreach ($acceptMethods as $method) {
            $this->acceptMethods[] = $method;
        }

        \array_unique($this->acceptMethods);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->paths;
    }

    private function paramMatch($match)
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }
        return '([^/]+)';
    }

    private function getParameters($callable)
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

    public function with($param, $regex)
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);
        return $this; // On retourne tjrs l'objet pour enchainer les arguments
    }

    public function getUrl($params)
    {
        $path = $this->paths;

        foreach ($params as $k => $v) {
            $path = str_replace("{$k}", $v, $path);
        }

        return $path === "" ? "/" : $path;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param mixed $router
     * @return Router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }
}
