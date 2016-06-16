<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\DataAccess\Connector\GuzzleConnector;

class ConnectorConfiguration extends Configuration
{
    protected static $connectorTemplates = [
        'event_source' => [
            'class' => GuzzleConnector::class,
            'settings' => [
                'transport' => 'http',
                'host' => '127.0.0.1',
                'port' => 5984,
                'database' => '',
                'status_test' => '/'
            ]
        ]
    ];

    public function apply(Injector $injector)
    {
        $injector->execute(function (array $crateConnectors = []) use ($injector) {
            $connDef = self::$connectorTemplates['event_source'];
            $connDef['settings']['database'] = sprintf(
                '%s-%s',
                StringToolkit::asSnakeCase($this->crate->getVendor()),
                StringToolkit::asSnakeCase($this->crate->getName())
            );
            $crateConnectors[$this->crate->getPrefix().'.event_source'] = $connDef;

            $crateConnectors = ($this->builder) ? $this->builder->build($crateConnectors) : $crateConnectors;
            $injector->defineParam('crateConnectors', $crateConnectors);
        });
    }
}
