<?php

if (!function_exists('route')) {
    function route($name, array $params = [])
    {
        global $app;
        return $app->getRouter()->getUrl($name, $params);
    }
}