<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorService;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ElasticsearchConnector;
use Honeybee\Infrastructure\DataAccess\Connector\Flysystem\LocalConnector;
use Honeybee\Infrastructure\DataAccess\Connector\Memory\ArrayConnector;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer\LocalSendmailConnector;
use Honeybee\Infrastructure\DataAccess\Connector\SwiftMailer\NullConnector;

class ConnectorServiceConfiguration extends Configuration
{
    protected static $defaultConnectors = [
        'honeybee.view_store' => [
            'class' => ElasticsearchConnector::class,
            'settings' => [
                'transport' => 'http',
                'host' => 'localhost',
                'port' => 9200,
                'index' => 'honeylex.domain_events'
            ]
        ],
        'honeybee.msg_queue' => [
            'class' => RabbitMqConnector::class,
            'settings' => [
                'host' => 'localhost',
                'port' => 5672,
                'user' => '%rabbitmq.user%',
                'password' => '%rabbitmq.password%'
            ]
        ],
        'honeybee.mailer' => [
            'class' => LocalSendmailConnector::class
        ],
        'honeybee.mailer.null' => [
            'class' => NullConnector::class
        ],
        'honeybee.files.local.tmp' => [
            'class' => LocalConnector::class,
            'settings' => [
                'directory' => '%project.dir%/data/tmp_files'
            ]
        ],
        'honeybee.process_state.store' => [
            'class' => LocalConnector::class,
            'settings' => [
                'directory' => '%project.dir%/data/process_states'
            ]
        ],
        'honeybee.process_state.cache' => [
            'class' => ArrayConnector::class
        ]
    ];

    public function apply(Injector $injector)
    {
        $injector
            ->share(ConnectorMap::class)
            ->prepare(
                ConnectorMap::class,
                function (ConnectorMap $map) use ($injector) {
                    $injector->execute([ $this, 'registerConnectors' ], [ ':connectorMap' => $map ]);
                }
            );

        $injector
            ->define(ConnectorService::class, [])
            ->share(ConnectorService::class)
            ->alias(ConnectorServiceInterface::class, ConnectorService::class);
    }

    public function registerConnectors(Injector $injector, array $connectors, ConnectorMap $connectorMap)
    {
        $connectors = $this->builder->build(array_merge_recursive(self::$defaultConnectors, $connectors));
        foreach ($connectors as $name => $config) {
            $connector = $config['class'];
            if (!class_exists($connector)) {
                throw new ConfigError(sprintf('Unable to load configured connector class: %s', $connector));
            }
            $connectorState = [
                ':name' => $name,
                ':config' => new ArrayConfig(isset($config['settings']) ? $config['settings'] : [])
            ];
            $dependencies = isset($config['dependencies']) ? $config['dependencies'] : [];
            foreach ($dependencies as $key => $dependency) {
                $connectorState[$key] = $dependency;
            }
            $connectorMap->setItem($name, $injector->make($connector, $connectorState));
        }
    }
}
