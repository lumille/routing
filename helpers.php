<?php

if (!function_exists('route')) {
    function route($name, array $params = [])
    {
        global $app;
        return $app->getRouter()->getUrl($name, $params);
    }
}

if (!function_exists('redirect'))
{
    function redirect($url, $params = [])
    {
        if (!preg_match('#^https?:\/\/#', $url)) {
            $url = route($url, $params) ?? $url;
        }

        header('Location: ' . $url);
        exit;
    }
}