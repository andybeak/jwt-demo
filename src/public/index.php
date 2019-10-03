<?php

require('../vendor/autoload.php');

use JWTDemo\App\Bootstrap;
use JWTDemo\App\Router;
use JWTDemo\App\Exceptions\EncodingException;
use JWTDemo\App\Exceptions\RouteException;

Bootstrap::loadConfig();

$container = Bootstrap::buildContainer();

$router = (new Router())
    ->setDispatcher($container['dispatcher'])
    ->setLogger($container['logger'])
    ->setHandler($container['handler']);

try {

    $routeInfo = $router->getRouteInfo();

    $router->dispatchRoute($routeInfo);

    http_response_code(200);

} catch (RouteException | EncodingException $e) {

    // we have sanitized the message so it's okay to display
    echo $e->getMessage();

    http_response_code(500);

} catch (Exception $e) {

    echo "Unexpected error";

    $container['logger']->error("Unexpected error: {$e->getMessage()}");

    http_response_code(500);

}
