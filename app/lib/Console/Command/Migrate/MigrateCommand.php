<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Migrate;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Migration\MigrationServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class MigrateCommand extends Command
{
    const ALL = 'all';

    const UP = 'up';

    const DOWN = 'down';

    protected $migrationService;

    public function __construct(MigrationServiceInterface $migrationService)
    {
        $this->migrationService = $migrationService;

        parent::__construct();
    }

    protected function migrate(OutputInterface $output, $direction, $target = null, $toVersion = null)
    {
        if ($target && $target !== self::ALL) {
            $migrationTarget = $this->migrationService->getMigrationTargetMap()->getItem($target);
        } else {
            $migrationTarget = null;
            if ($toVersion !== null) {
                $output->writeln('<error>Version parameter only supported together with a valid target.</error>');
                return;
            }
        }
        if ($toVersion !== null) {
            $latestVersion = $migrationTarget->getLatestStructureVersion()->getVersion();
            if ($direction === self::UP && (int)$latestVersion >= (int)$toVersion) {
                $output->writeln(
                    '<error>The version to migrate to must not be smaller than the current head version.</error>'
                );
                return;
            } elseif ($direction === self::DOWN && (int)$latestVersion <= (int)$toVersion) {
                $output->writeln(
                    '<error>The version to migrate to must not be greater than the current head version.</error>'
                );
                return;
            }
        }

        if (!$migrationTarget) {
            foreach ($this->migrationService->getMigrationTargetMap() as $targetName => $migrationTarget) {
                if ($migrationTarget->isActivated()) {
                    $output->writeln('Running migrations for "'.$targetName.'"');
                    foreach ($this->migrationService->migrate($targetName) as $runMigration) {
                        $output->writeln('Executed migration "'.$runMigration->getName().'"');
                    }
                }
            }
        } else {
            foreach ($this->migrationService->migrate($migrationTarget->getName(), $toVersion) as $runMigration) {
                $output->writeln('Executed migration "'.$runMigration->getName().'"');
            }
        }
    }
}
