<?php


namespace Lumille\Routing\tests;


use Lumille\Routing\MethodNotAcceptedException;
use Lumille\Routing\RouteCollection;
use Lumille\Routing\Router;
use Lumille\Routing\RouterException;
use Lumille\Routing\UrlNotFoundException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Exception;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends TestCase
{

    public function testOptionalParams(){
        $request = Request::create('/hello/say-hello/fred', "GET");
        $route = new Router($request);
        $route->get('/hello/{slug?}/{name?}', 'HomeController::index');
        list($callable, $args) = $route->run();
        $this->assertEquals('HomeController::index', $callable);
    }

    public function testGetHome ()
    {
        $request = Request::create('/', "GET");
        $route = new Router($request);
        $route->get('/', 'HomeController::index');
        list($callable, $args) = $route->run();
        $this->assertEquals('HomeController::index', $callable);
    }

    public function testErrorMethodGetHome ()
    {
        $this->expectException(MethodNotAcceptedException::class);
        $request = Request::create('/', "PATCH");
        $route = new Router($request);
        $route->get('/', 'HomeController::index');
        list($callable, $args) = $route->run();
    }

    public function testPostHome ()
    {
        $request = Request::create('/', "POST");
        $route = new Router($request);
        $route->post('/', 'HomeController::index');
        list($callable, $args) = $route->run();
        $this->assertEquals('HomeController::index', $callable);
    }

    public function testGetQuery ()
    {
        $request = Request::create('/about?say=hello', "GET");
        $route = new Router($request);
        $route->get('/about', 'AboutController::index');
        list($callable, $args) = $route->run();
        $this->assertEquals('AboutController::index', $callable);
    }

    public function testGetParam ()
    {
        $request = Request::create('/hello/fred', "GET");
        $route = new Router($request);
        $route->get('/hello/{name}', 'HelloController::index');
        list(, $args) = $route->run();
        $this->assertContains('fred', $args);
    }

    public function testGetNestedRoutes ()
    {
        $request = Request::create('/a/b', "GET");
        $route = new Router($request);
        $route->prefix('/a', function ($route) {
            $route->prefix('/b', function ($route) {
                $route->get('/', 'OtherController::index');
            });
        });
        list($callable) = $route->run();
        $this->assertEquals('OtherController::index', $callable);
    }

    public function testErrorMethod ()
    {
        $this->expectException(MethodNotAcceptedException::class);
        $request = Request::create('/error-method', "GET");
        $route = new Router($request);
        $route->post('/error-method', 'HomeController::index');
        list($callable, $args) = $route->run();
    }

    public function testErrorUrlNotFound()
    {
        $this->expectException(UrlNotFoundException::class);
        $request = Request::create('/ssssssss', "GET");
        $route = new Router($request);
        $route->post('/homr', 'HomeController::index');
        list($callable, $args) = $route->run();
    }
}