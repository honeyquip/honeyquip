<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use League\Plates\Engine;

class PlatesConfiguration implements ConfigurationInterface
{
    private $configBag;

    public function __construct(ConfigBagInterface $configBag)
    {
        $this->configBag = $configBag;
    }

    public function apply(Injector $injector)
    {
        $injector->define(Engine::class, [
            ':directory' => $this->configBag->get('plates.directory')
        ]);
    }
}
