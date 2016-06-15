<?php

// Include Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

$context = 'web';
$environment = 'development';
$appDir = dirname(__DIR__);

Equip\Application::build()
    ->setConfiguration([
        Equip\Configuration\AurynConfiguration::class,
        Equip\Configuration\DiactorosConfiguration::class,
        Equip\Configuration\EnvConfiguration::class,
        Honeybee\FrameworkBinding\Equip\Configuration\ConfigBagConfiguration::class,
        Equip\Configuration\MonologConfiguration::class,
        Honeybee\FrameworkBinding\Equip\Configuration\CrateConfiguration::class,
        Equip\Configuration\PayloadConfiguration::class,
        Honeybee\FrameworkBinding\Equip\Configuration\ConnectorServiceConfiguration::class,
        Honeybee\FrameworkBinding\Equip\Configuration\DataAccessServiceConfiguration::class,
        Honeybee\FrameworkBinding\Equip\Configuration\PlatesConfiguration::class,
        Equip\Configuration\PlatesResponderConfiguration::class,
        Equip\Configuration\RelayConfiguration::class,
        Equip\Configuration\WhoopsConfiguration::class,
    ])
    ->setMiddleware([
        Relay\Middleware\ResponseSender::class,
        Equip\Handler\ExceptionHandler::class,
        Honeybee\FrameworkBinding\Equip\Handler\CrateDispatchHandler::class,
        Equip\Handler\JsonContentHandler::class,
        Equip\Handler\FormContentHandler::class,
        Equip\Handler\ActionHandler::class,
    ])
    ->setRouting(function (Equip\Directory $directory) {
        return $directory
            ->get('/hello[/{name}]', Honeybee\FrameworkBinding\Equip\Domain\Hello::class)
            ->post('/hello[/{name}]', Honeybee\FrameworkBinding\Equip\Domain\Hello::class)
        ;
    })
    ->run()
;
