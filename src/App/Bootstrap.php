<?php

namespace JWTDemo\App;

use Pimple\Container;
use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Bootstrap
{
    /**
     * Load config environment variables
     */
    public static function loadConfig()
    {
        $dotenv = Dotenv::create(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $dotenv->load();
    }

    /**
     * Create and return the container interface
     * @return Container
     */
    public static function buildContainer(): Container
    {
        // construct DI container
        $container = new Container();

        $container['logger'] = function () {
            $logger = new Logger('my_logger');
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../log/' . date('Y-m-d') . '.log', Logger::DEBUG));
            return $logger;
        };

        $container['dispatcher'] = function() {
            return \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
                $r->addRoute('GET', '/create', 'create_jwt');
                $r->addRoute('GET', '/check/{token}', 'check_jwt');
                $r->addRoute('GET', '/logout/{token}', 'logout_handler');
            });
        };

        $container['jwt_wrapper'] = function($container) {
            return (new JWTwrap())->setLogger($container['logger']);
        };

        $container['token_blacklist'] = function() {
            $tokenBlacklist = new TokenBlacklist();
            $tokenBlacklist->loadFromFile();
            $tokenBlacklist->expireOldTokens();
            return $tokenBlacklist;
        };

        $container['handler'] = function($container) {
            return (new Handler())
                ->setLogger($container['logger'])
                ->setJWTwrap($container['jwt_wrapper'])
                ->setTokenBlacklist($container['token_blacklist']);
        };



        return $container;
    }

}