<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;
use Honeybee\Infrastructure\DataAccess\Connector\GuzzleConnector;

class ConnectorConfiguration implements ConfigurationInterface
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

    protected $crate;

    public function __construct(CrateInterface $crate)
    {
        $this->crate = $crate;
    }

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
            // $connectionConfigs will be merged in later by the ConnectorServiceConfiguration
            $injector->defineParam('crateConnectors', $crateConnectors);
        });
    }
}
