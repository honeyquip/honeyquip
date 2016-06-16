<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationSet;
use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\CommandBusConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\ConnectorConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\DataAccessConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\MigrationConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\PlatesConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\ResourceTypeConfiguration;
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

    private $routes = [];

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

    protected function provideConfiguration()
    {
        $configs = [
            'resource_type' => ResourceTypeConfiguration::forCrate($this),
            'connector' => ConnectorConfiguration::forCrate($this),
            'data_access' => DataAccessConfiguration::forCrate($this),
            'migration' => MigrationConfiguration::forCrate($this),
            'command_bus' => CommandBusConfiguration::forCrate($this),
            'plates' => PlatesConfiguration::forCrate($this)
        ];

        return [ $this->routes, $configs ];
    }

    public function configure(Injector $injector)
    {
        list($routes, $configs) = $this->provideConfiguration();
        foreach ($configs as $configuration) {
            $configuration->apply($injector);
        }
        $this->addRoutes($routes);
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

    protected function addRoutes(array $routes)
    {
        $this->routes = array_merge($this->routes, $routes);
    }
}
