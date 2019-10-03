<?php

namespace JWTDemo\App;

use FastRoute\Dispatcher\GroupCountBased;
use Psr\Log\LoggerInterface;
use JWTDemo\App\Exceptions\RouteException;
use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\Request;

class Router
{
    /**
     * @var GroupCountBased
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Handler
     */
    private $handler;


    /**
     * @return array
     * @throws RouteException
     */
    public function getRouteInfo(): array
    {
        try {

            $httpMethod = $_SERVER['REQUEST_METHOD'];

            $uri = $_SERVER['REQUEST_URI'];

            // Strip query string (?foo=bar) and decode URI
            if (false !== $pos = strpos($uri, '?')) {
                $uri = substr($uri, 0, $pos);
            }
            $uri = rawurldecode($uri);

            $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

            return $routeInfo;

        } catch (\Throwable $e) {

            $this->logger->error(__METHOD__ . ' : ' . $e->getMessage());

            throw new RouteException('Could not route request');
        }

    }

    /**
     * @param $routeInfo
     * @throws RouteException
     */
    public function dispatchRoute($routeInfo): void
    {

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo "404 - Not found";
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                http_response_code(405);
                echo "405 - Method not allowed";
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $request = Request::createFromGlobals();
                $handlerRoutine = $routeInfo[1];
                $this->handler->$handlerRoutine($request, $vars);
                break;
        }

    }


    /**
     * Setter injection
     * @param GroupCountBased $dispatcher
     * @return Router
     */
    public function setDispatcher(GroupCountBased $dispatcher): Router
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Setter injection
     * @param LoggerInterface $logger
     * @return Router
     */
    public function setLogger(LoggerInterface $logger): Router
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Handler $handler
     * @return Router
     */
    public function setHandler(Handler $handler): Router
    {
        $this->handler = $handler;

        return $this;
    }

}