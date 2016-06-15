<?php

namespace Foh\SystemAccount;

use Auryn\Injector;
use Foh\SystemAccount\Endpoint\UserList;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\ConnectorConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\DataAccessConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\MigrationConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\PlatesConfiguration;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\ResourceTypeConfiguration;
use Honeybee\FrameworkBinding\Equip\Crate\Crate;

class SystemAccountCrate extends Crate
{
    protected function provideConfiguration()
    {
        return [
            new ResourceTypeConfiguration($this),
            new ConnectorConfiguration($this),
            new DataAccessConfiguration($this),
            new MigrationConfiguration($this),
            new PlatesConfiguration($this)
        ];
    }

    protected function provideRoutes()
    {
        return [
            'GET /hello[/{name}]' => UserList::class
        ];
    }
}
