<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command;

use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    protected $configBag;

    public function __construct(ConfigBagInterface $configBag)
    {
        $this->configBag = $configBag;

        parent::__construct();
    }
}
