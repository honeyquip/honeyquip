<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationSet;
use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\Crate\EntityTypeLoaderInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

abstract class Crate implements CrateInterface
{
    private $manifest;

    private $typeLoader;

    private $routePrefix;

    private $aggregateRootTypeMap;

    private $projectionTypeMap;

    abstract protected function provideConfiguration();

    abstract protected function provideRoutes();

    public function __construct(
        CrateManifestInterface $manifest,
        EntityTypeLoaderInterface $typeLoader,
        $routePrefix = null
    ) {
        $this->manifest = $manifest;
        $this->typeLoader = $typeLoader;
        $this->routePrefix = $routePrefix ?: $this->getPrefix();
        $this->aggregateRootTypeMap = $this->typeLoader->loadAggregateRootTypes($this);
        $this->projectionTypeMap = $this->typeLoader->loadProjectionTypes($this);
    }

    public function configure(Injector $injector)
    {
        $this->routes = $this->provideRoutes();
        $configs = new ConfigurationSet($this->provideConfiguration());
        $configs->apply($injector);
    }

    public function dispatch(ServerRequestInterface $request)
    {
        $route = $this->createDispatcher()->dispatch($request->getMethod(), $request->getUri()->getPath());
        $status = array_shift($route);
        if (Dispatcher::FOUND === $status) {
            return $route;
        }

        // handle method not allowed
        return null;
    }

    public function getAggregateRootTypes()
    {
        return $this->aggregateRootTypeMap;
    }

    public function getProjectionTypes()
    {
        return $this->projectionTypeMap;
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function getConfigDir()
    {
        return $this->getRootDir() . '/config';
    }

    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->manifest, $method)) {
            throw new RuntimeError(
                sprintf(
                    'Method "%s" does not exist on "%s" or "%s".',
                    $method,
                    get_class($this),
                    get_class($this->manifest)
                )
            );
        }

        return call_user_func_array(array($this->manifest, $method), $arguments);
    }

    protected function createDispatcher()
    {
        return FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->routes as $request => $action) {
                list($method, $path) = explode(' ', $request, 2);
                $collector->addRoute('GET', $this->getRoutePrefix().$path, $action);
            }
        });
    }
}
