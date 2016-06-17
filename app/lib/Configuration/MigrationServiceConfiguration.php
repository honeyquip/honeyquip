<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Migration\MigrationService;
use Honeybee\Infrastructure\Migration\MigrationServiceInterface;
use Honeybee\Infrastructure\Migration\MigrationTarget;
use Honeybee\Infrastructure\Migration\MigrationTargetMap;

class MigrationServiceConfiguration extends Configuration
{
    public function apply(Injector $injector)
    {
        $injector
            ->prepare(
                MigrationTargetMap::class,
                function (MigrationTargetMap $map) use ($injector) {
                    $injector->execute(function (array $migrationTargets = []) use ($injector, $map) {
                        $migrationTargets = $this->builder->build($migrationTargets);
                        foreach ($migrationTargets as $targetName => $targetConfig) {
                            $loader = $this->buildMigrationLoader($injector, $targetConfig['migration_loader']);
                            $migrationTarget = $injector->make(MigrationTarget::class, [
                                ':name'             => $targetName,
                                ':config'           => new ArrayConfig($targetConfig['settings']),
                                ':is_activated'     => $targetConfig['active'],
                                ':migration_loader' => $loader

                            ]);
                            $map->setItem($targetName, $migrationTarget);
                        }
                    });
                }
            )
            ->alias(MigrationServiceInterface::class, MigrationService::class)
            ->define(MigrationServiceInterface::class, [ ':config' => new ArrayConfig([]) ])
            ->share(MigrationServiceInterface::class);
    }

    protected function buildMigrationLoader(Injector $injector, array $config)
    {
        $class = $config['class'];
        if (!class_exists($class)) {
            throw new ConfigError(sprintf("Unable to load configured collector class: %s", $class));
        }
        $state = [
            ':config' => new ArrayConfig(isset($config['settings']) ? $config['settings'] : []),
            ':injector' => $injector
        ];

        return $injector->make($class, $state);
    }
}
