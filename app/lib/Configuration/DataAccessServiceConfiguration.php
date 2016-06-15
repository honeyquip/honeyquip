<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessService;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\DomainEvent\DomainEventFinder;
use Honeybee\Infrastructure\DataAccess\Finder\FinderMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\DomainEvent\DomainEventWriter;
use Honeybee\Infrastructure\DataAccess\Storage\Filesystem\ProcessState\ProcessStateReader;
use Honeybee\Infrastructure\DataAccess\Storage\Filesystem\ProcessState\ProcessStateWriter;
use Honeybee\Infrastructure\DataAccess\Storage\Memory\ProcessState\ProcessStateReader as ProcessStateCacheReader;
use Honeybee\Infrastructure\DataAccess\Storage\Memory\ProcessState\ProcessStateWriter as ProcessStateCacheWriter;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkMap;
use Trellis\Common\Collection\Map;

class DataAccessServiceConfiguration implements ConfigurationInterface
{
    protected static $defaultReaders = [
        'honeybee.core::process_state::process_store::reader' => [
            'class' => ProcessStateReader::class,
            'connection' => 'honeybee.process_state.store'
        ],
        'honeybee.core::process_state::process_cache::reader' => [
            'class' => ProcessStateCacheReader::class,
            'connection' => 'honeybee.process_state.cache'
        ]
    ];

    protected static $defaultWriters = [
        'honeybee::domain_event::view_store::writer' => [
            'class' => DomainEventWriter::class,
            'connection' => 'honeybee.view_store',
            'settings' => [ 'type' => 'domain_event' ]
        ],
        'honeybee.core::process_state::process_store::writer' => [
            'class' => ProcessStateWriter::class,
            'connection' => 'honeybee.process_state.store'
        ],
        'honeybee.core::process_state::process_cache::writer' => [
            'class' => ProcessStateCacheWriter::class,
            'connection' => 'honeybee.process_state.cache'
        ],
    ];

    protected static $defaultFinders = [
        'honeybee::domain_event::view_store::finder' => [
            'class' => DomainEventFinder::class,
            'connection' => 'honeybee.view_store',
            'settings' => [ 'type' => 'domain_event' ]
        ]
    ];

    protected static $defaultQueryServices = [];

    protected static $defaultUows = [];

    public function apply(Injector $injector)
    {
        $injector->delegate(
            StorageReaderMap::CLASS,
            function (ConnectorServiceInterface $connectors, array $storageReaders = []) use ($injector) {
                $configs = array_merge(self::$defaultReaders, $storageReaders);
                return $this->initStorageMap(new StorageReaderMap, self::$defaultReaders, $connectors, $injector);
            }
        )->share(StorageReaderMap::CLASS);
        $injector->delegate(
            StorageWriterMap::CLASS,
            function (ConnectorServiceInterface $connectors, array $storageWriters = []) use ($injector) {
                $configs = array_merge(self::$defaultWriters, $storageWriters);
                return $this->initStorageMap(new StorageWriterMap, $configs, $connectors, $injector);
            }
        )->share(StorageWriterMap::CLASS);
        $this->registerUowMapDelegate($injector);

        $injector->delegate(
            FinderMap::CLASS,
            function (ConnectorServiceInterface $connectors, array $finders = []) use ($injector) {
                $configs = array_merge(self::$defaultFinders, $finders);
                return $this->initStorageMap(new FinderMap, $configs, $connectors, $injector);
            }
        )->share(FinderMap::CLASS);
        $this->registerQueryServiceMapDelegate($injector);

        $injector->share(DataAccessServiceInterface::class)
            ->alias(DataAccessServiceInterface::CLASS, DataAccessService::class);
    }

    public function initStorageMap(Map $map, array $configs, ConnectorServiceInterface $connectors, Injector $injector)
    {
        foreach ($configs as $key => $config) {
            $storageState = [
                ':config' => new ArrayConfig(isset($config['settings']) ? $config['settings'] : []),
                ':connector' => $connectors->getConnector($config['connection'])
            ];
            if (isset($config['dependencies'])) {
                foreach ($config['dependencies'] as $key => $dependency) {
                    $storageState[$key] = $dependency;
                }
            }
            $map->setItem($key, $injector->make($config['class'], $storageState));
        }
        return $map;
    }

    protected function registerUowMapDelegate(Injector $injector)
    {
        $injector->delegate(
            UnitOfWorkMap::CLASS,
            function (StorageWriterMap $storageWriterMap, StorageReaderMap $storageReaderMap) use ($injector) {
                $map = new UnitOfWorkMap;
                foreach (self::$defaultUows as $uowKey => $uowConf) {
                    $objectState = [
                        ':config' => new ArrayConfig(isset($uowConf['settings']) ? $uowConf['settings'] : []),
                        ':event_reader' => $storageReaderMap->getItem($uowConf['event_reader']),
                        ':event_writer' => $storageWriterMap->getItem($uowConf['event_writer'])
                    ];
                    if (isset($uowConf['dependencies'])) {
                        foreach ($uowConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map->setItem($uowKey, $injector->make($uowConf['class'], $objectState));
                }
                return $map;
            }
        )->share(UnitOfWorkMap::CLASS);
    }

    protected function registerQueryServiceMapDelegate(Injector $injector)
    {
        $injector->delegate(
            QueryServiceMap::CLASS,
            function (FinderMap $finderMap) use ($injector) {
                $queryServiceMap = new QueryServiceMap;
                foreach (self::$defaultQueryServices as $serviceKey => $qsConf) {
                    $finderMappings = [];
                    foreach ($qsConf['finder_mappings'] as $finderMappingName => $finderMapping) {
                        $finderMappings[$finderMappingName] = [
                            'finder' => $finderMap->getItem($finderMapping['finder']),
                            'query_translation' => $this->createQueryTranslation(
                                $finderMapping['query_translation']
                            )
                        ];
                    }
                    $objectState =[
                        ':config' => new ArrayConfig(isset($qsConf['settings']) ? $qsConf['settings'] : []),
                        ':finder_mappings' => $finderMappings
                    ];
                    if (isset($qsConf['dependencies'])) {
                        foreach ($qsConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $queryServiceMap->setItem($serviceKey, $injector->make($qsConf['class'], $objectState));
                }

                return $queryServiceMap;
            }
        )->share(QueryServiceMap::CLASS);
    }

    protected function createQueryTranslation(array $config)
    {
        $class = $config['class'];
        if (!$class) {
            throw new ConfigError('Missing setting "query_translation" within ' . static::CLASS);
        }
        if (!class_exists($class)) {
            throw new ConfigError(sprintf('Configured query-translation: "%s" does not exist!', $class));
        }
        $settings = isset($config['settings']) ? $config['settings'] : [];

        $queryTranslation = new $class(new ArrayConfig($settings));
        if (!$queryTranslation instanceof QueryTranslationInterface) {
            throw new ConfigError(
                sprintf(
                    'Configured query-translation %s does not implement %s',
                    get_class($queryTranslation),
                    QueryTranslationInterface::CLASS
                )
            );
        }
        return $queryTranslation;
    }
}
