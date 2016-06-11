<?php

namespace Honeybee\FrameworkBinding\Equip\Handler;

use Equip\Action;
use Equip\Directory;
use Equip\Exception\HttpException;
use Equip\Handler\ActionHandler;
use Equip\Handler\DispatchHandler;
use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CrateDispatchHandler
{
    private $crateMap;

    private $directory;

    public function __construct(CrateMap $crateMap, Directory $directory)
    {
        $this->crateMap = $crateMap;
        $this->directory = $directory;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $action = null;
        foreach ($this->crateMap as $crate) {
            $matchedAction = $crate->dispatch($request);
            if ($matchedAction) {
                $action = new Action($matchedAction[0]);
                $args = $matchedAction[1];
                break;
            }
        }
        if (!$action) {
            list($action, $args) = $this->dispatch(
                $this->dispatcher(),
                $request->getMethod(),
                $request->getUri()->getPath()
            );
        }
        $request = $request->withAttribute(ActionHandler::ACTION_ATTRIBUTE, $action);

        foreach ($args as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $next($request, $response);
    }

    protected function dispatcher()
    {
        return FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->directory as $request => $action) {
                list($method, $path) = explode(' ', $request, 2);
                $collector->addRoute($method, $path, $action);
            }
        });
    }

    private function dispatch(Dispatcher $dispatcher, $method, $path)
    {
        $route = $dispatcher->dispatch($method, $path);
        $status = array_shift($route);

        if (Dispatcher::FOUND === $status) {
            return $route;
        }

        if (Dispatcher::METHOD_NOT_ALLOWED === $status) {
            $allowed = array_shift($route);
            throw HttpException::methodNotAllowed($path, $method, $allowed);
        }

        throw HttpException::notFound($path);
    }
}
