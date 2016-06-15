<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Console\Scafold\SkeletonGenerator;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RemoveResource extends ResourceCommand
{
    protected $crateMap;

    public function __construct(ConfigBagInterface $configBag, CrateMap $crateMap)
    {
        parent::__construct($configBag);

        $this->crateMap = $crateMap;
    }

    protected function configure()
    {
        $this
            ->setName('hqp:res:rm')
            ->setDescription('Removes a specific resource from the given crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                "The prefix of the crate to remove the resource from."
            )
            ->addArgument(
                'resource',
                null,
                InputArgument::REQUIRED,
                "The name of the resource to remove."
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
        $resourceDirectories = [
            $crate->getRootDir().'/config/'.$resourceName,
            $crate->getRootDir().'/lib/'.$resourceName,
            $crate->getRootDir().'/templates/'.StringToolkit::asSnakeCase($resourceName)
        ];
        // @todo tricky: find and remove proper migration directories
        foreach ($resourceDirectories as $resourceDirectory) {
            $output->writeln('<info>Removing resource dir '.$resourceDirectory.'</info>');
            (new Filesystem)->remove($resourceDirectory);
        }
        // @todo also tricky: find and run proper migrations
    }
}
