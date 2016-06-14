<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Crate;

use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCrates extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hqp:crate:ls')
            ->setDescription('Lists currently installed crates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->crateMap as $crate) {
            $this->printCrateInfo($crate, $output);
        }
    }

    protected function printCrateInfo(CrateInterface $crate, OutputInterface $output)
    {
        $output->writeln('- '.$crate->getName().': '.$crate->getRootDir());
    }
}
