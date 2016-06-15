<?php

namespace Foh\SystemAccount;

use Auryn\Injector;
use Foh\SystemAccount\Domain\Index;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\ConnectorConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\DataAccessConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\PlatesConfiguration;
use Honeybee\FrameworkBinding\Equip\Crate\Crate;

class SystemAccountCrate extends Crate
{
    protected function provideConfiguration(Injector $injector)
    {
        return [
            new ConnectorConfiguration($this),
            new DataAccessConfiguration($this),
            new PlatesConfiguration($this)
        ];
    }

    protected function provideRoutes(Injector $injector)
    {
        return [
            'GET /hello[/{name}]' => Index::class
        ];
    }
}
