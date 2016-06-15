<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Console\Scafold\SkeletonGenerator;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ResourceInfo extends ResourceCommand
{
    protected $projectionTypeMap;

    protected $aggregateRootTypeMap;

    protected $crateMap;

    public function __construct(
        ConfigBagInterface $configBag,
        ProjectionTypeMap $projectionTypeMap,
        AggregateRootTypeMap $aggregateRootTypeMap,
        CrateMap $crateMap
    ) {
        parent::__construct($configBag);

        $this->projectionTypeMap = $projectionTypeMap;
        $this->aggregateRootTypeMap = $aggregateRootTypeMap;
        $this->crateMap = $crateMap;
    }

    protected function configure()
    {
        $this
            ->setName('hqp:res:info')
            ->setDescription('Displays detail information about a specific resource from the given crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                "The prefix of the crate that contains the target resource."
            )
            ->addArgument(
                'resource',
                null,
                InputArgument::REQUIRED,
                "The name of the resource to display the details for."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');
        $crate = $this->crateMap->getItem($cratePrefix);
        if (!$resourceName || !$cratePrefix || !$crate) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

        $crateDir = $crate->getRootDir();
        $resourcePrefix = $crate->getPrefix().'.'.StringToolkit::asSnakeCase($resourceName);
        $projectionType = $this->projectionTypeMap->getItem($resourcePrefix);
        $aggregateRootType = $this->aggregateRootTypeMap->getItem($resourcePrefix);

        $resourceDirectories = [
            $crateDir.'config/'.$resourceName,
            $crateDir.'lib/'.$resourceName,
            $crateDir.'templates/'.StringToolkit::asSnakeCase($resourceName)
        ];

        $output->writeln('Crate:       ' . $crate->getVendor().'/'.$crate->getName());
        $output->writeln('Name:        ' . $resourceName);
        $output->writeln('Namespace:   ' . $crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Projection:  ' . $projectionType->getEntityImplementor());
        $output->writeln('Model:       ' . $aggregateRootType->getEntityImplementor());
        $output->writeln('Directories: ');
        $output->writeln('- '.$crateDir.'/config/'.$resourceName);
        $output->writeln('- '.$crateDir.'/lib/'.$resourceName);
        $output->writeln('- '.$crateDir.'/templates/'.StringToolkit::asSnakeCase($resourceName));
    }
}
