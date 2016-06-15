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
        $injector->delegate(
            Engine::class,
            function (Injector $injector, array $templateFolders = []) {
                $platesEngine = new Engine($this->configBag->get('plates.directory'));
                foreach ($templateFolders as $name => $folder) {
                    $platesEngine->addFolder($name, $folder);
                }
                return $platesEngine;
            }
        );
    }
}
