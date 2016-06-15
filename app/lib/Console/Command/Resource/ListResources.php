<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Resource;

use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;

class ListResources extends ResourceCommand
{
    protected $fileFinder;

    protected $crateMap;

    public function __construct(ConfigBagInterface $configBag, CrateMap $crateMap, Finder $fileFinder)
    {
        parent::__construct($configBag);

        $this->crateMap = $crateMap;
        $this->fileFinder = $fileFinder;
    }

    protected function configure()
    {
        $this
            ->setName('hqp:res:ls')
            ->setDescription('Lists all resources within a given crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                "The prefix of the crate to remove the resource from."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        if ($cratePrefix) {
            $crates = [ $cratePrefix ];
        } else {
            $crates = $this->crateMap->getKeys();
        }
        foreach ($crates as $cratePrefix) {
            $crate = $this->crateMap->getItem($cratePrefix);
            $finder = clone $this->fileFinder;
            $foundSchemas = $finder->in($crate->getRootDir())->name('aggregate_root.xml');
            $output->writeln($crate->getVendor().'/'.$crate->getName());
            foreach (iterator_to_array($foundSchemas, true) as $fileInfo) {
                $entitySchema = (new EntityTypeSchemaXmlParser)->parse($fileInfo->getPathname());
                $typeDefinition = $entitySchema->getEntityTypeDefinition();
                $output->writeln('- Name: ' . $typeDefinition->getName());
                $output->writeln('  Description: ' . implode(PHP_EOL, $typeDefinition->getDescription()));
            }
        }
    }
}
