<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;

class PlatesConfiguration extends Configuration
{
    public function apply(Injector $injector)
    {
        $injector->execute(function (array $templateFolders = []) use ($injector) {
            $templateFolders[$this->crate->getPrefix()] = $this->crate->getRootDir().'/templates';
            $templateFolders = ($this->builder) ? $this->builder->build($templateFolders) : $templateFolders;
            $injector->defineParam('templateFolders', $templateFolders);
        });
    }
}
