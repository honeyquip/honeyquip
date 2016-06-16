<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Infrastructure\Command\Bus\CommandBus;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\Bus\Subscription\LazyCommandSubscription;
use Honeybee\Infrastructure\Command\Bus\Transport\JobQueueTransport;
use Honeybee\Infrastructure\Command\Bus\Transport\SynchronousTransport;

class CommandBusConfiguration implements ConfigurationInterface
{
    protected static $defaultConfig = [
        'transports' => [
            'sync' => [
                'class' => SynchronousTransport::class
            ],
            /*'spinner_default' => [
                'class' => JobQueueTransport::class,
                'settings' => [
                    'exchange' => 'honeybee.domain.commands'
                ]
            ]*/
        ],
        'subscriptions' => []
    ];

    public function apply(Injector $injector)
    {
        $factory = function (CommandBusInterface $commandBus) use ($injector) {
            $injector->execute(function (array $commandBusConfig = []) use ($injector, $commandBus) {
                $this->prepareCommandBus($injector, $commandBus, array_merge(self::$defaultConfig, $commandBusConfig));
            });
        };

        $injector
            ->prepare(CommandBus::class, $factory)
            ->alias(CommandBusInterface::class, CommandBus::class)
            ->share(CommandBusInterface::class);
    }

    protected function prepareCommandBus(Injector $injector, CommandBusInterface $commandBus, array $config)
    {
        $builtTransports = [];
        foreach ($config['transports'] as $transportName => $transportConfig) {
            if (!isset($builtTransports[$transportName])) {
                $builtTransports[$transportName] = $this->buildTransport(
                    $injector,
                    $transportName,
                    $transportConfig,
                    $commandBus
                );
            }
        }

        foreach ($config['subscriptions'] as $subscriptionConfig) {
            $transport = $builtTransports[$subscriptionConfig['transport']];
            foreach ($subscriptionConfig['commands'] as $commandType => $commandConfig) {
                $commandBus->subscribe(
                    $injector->make(
                        LazyCommandSubscription::CLASS,
                        [
                            ':command_type' => $commandType,
                            ':command_transport' => $transport,
                            ':command_handler_callback' => function () use ($injector, $commandConfig) {
                                return $injector->make($commandConfig['handler']);
                            }
                        ]
                    )
                );
            }
        }
    }

    protected function buildTransport(
        Injector $injector,
        $transportName,
        array $transportConfig,
        CommandBusInterface $commandBus
    ) {
        $transportState = [ ':name' => $transportName, ':command_bus' => $commandBus ];
        $settings = isset($transportConfig['settings']) ? $transportConfig['settings'] : [];
        foreach ($settings as $propName => $propValue) {
            $transportState[':' . $propName] = $propValue;
        }

        return $injector->make($transportConfig['class'], $transportState);
    }
}
