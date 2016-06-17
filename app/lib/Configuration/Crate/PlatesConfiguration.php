<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;

class PlatesConfiguration extends CrateConfiguration
{
    public function apply(Injector $injector)
    {
        $injector->execute(function (array $templateFolders = []) use ($injector) {
            $templateFolders[$this->crate->getPrefix()] = $this->crate->getRootDir().'/templates';
            $injector->defineParam('templateFolders', $this->builder->build($templateFolders));
        });
    }
}
