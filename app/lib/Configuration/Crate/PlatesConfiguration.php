<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;

class PlatesConfiguration implements ConfigurationInterface
{
    protected $crate;

    public function __construct(CrateInterface $crate)
    {
        $this->crate = $crate;
    }

    public function apply(Injector $injector)
    {
        $injector->execute(function (array $templateFolders = []) use ($injector) {
            $templateFolders[$this->crate->getPrefix()] = $this->crate->getRootDir().'/templates';
            // $templateFolders will be merged in later by the PlatesConfiguration
            $injector->defineParam('templateFolders', $templateFolders);
        });
    }
}
