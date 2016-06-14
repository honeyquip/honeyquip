<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Migrate;

use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Shrink0r\Monatic\Maybe;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListTargets extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hqp:migrate:ls')
            ->setDescription('Lists available migration targets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->migrationService->getMigrationTargetMap() as $migrationTarget) {
            $this->printMigrationTarget($migrationTarget, $output);
        }
    }

    protected function printMigrationTarget(MigrationTargetInterface $target, OutputInterface $output)
    {
        $pendingCount = $this->migrationService->getPendingMigrations($target->getName())->getSize();
        $latestVersion = Maybe::unit($target->getLatestStructureVersion())->getVersion()->get() ?: 0;
        $output->writeln($target->getName());
        $output->writeln('  version: '.$latestVersion);
        $output->writeln('  active: '.($target->isActivated() ? 'true' : 'false'));
        if ($pendingCount === 0) {
            $output->writeln('  migrations: '.$target->getMigrationList()->getSize());
        } else {
            $output->writeln('  migrations: '.$target->getMigrationList()->getSize().'/'.$pendingCount.' (pending)');
        }
    }
}
