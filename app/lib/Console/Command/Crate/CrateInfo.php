<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Crate;

use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrateInfo extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hqp:crate:info')
            ->setDescription('Displays details for the specified crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                'The prefix of the crate to show the details for.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getArgument('crate');
        $crate = $this->crateMap->getItem($prefix);
        if ($crate) {
            $this->printCrateInfo($crate, $output);
        } else {
            $output->writeln('<error>Given crate is not installed.</error>');
        }
    }

    protected function printCrateInfo(CrateInterface $crate, OutputInterface $output)
    {
        $output->writeln($crate->getName().": ".$crate->getDescription());
        $output->writeln('  prefix: '.$crate->getPrefix());
        $output->writeln('  namespace: '.$crate->getNamespace());
        $output->writeln('  directory: '.$crate->getRootDir());
    }
}
