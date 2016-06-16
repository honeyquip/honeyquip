<?php

namespace Foh\SystemAccount;

use Auryn\Injector;
use Foh\SystemAccount\Endpoint\UserList;
use Foh\SystemAccount\User\Model\Task\CreateUser\CreateUserCommandHandler;
use Foh\SystemAccount\User\Model\Task\ModifyUser\ModifyUserCommandHandler;
use Foh\SystemAccount\User\Model\Task\ProceedUserWorkflow\ProceedUserWorkflowCommandHandler;
use Honeybee\FrameworkBinding\Equip\Configuration\Crate\CommandBusConfiguration;
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
        list($routes, $configs) = parent::provideConfiguration();

        $configs['command_bus']->extend()
            ->subscriptions[0]
                ->transport('sync')
                ->commands([
                    'foh.system_account.user.create_user' => [
                        'handler' => CreateUserCommandHandler::class
                    ],
                    'foh.system_account.user.modify_user' => [
                        'handler' => ModifyUserCommandHandler::class
                    ],
                    'foh.system_account.user.proceed_user_workflow' => [
                        'handler' => ProceedUserWorkflowCommandHandler::class
                    ]
                ]);

        $routes['GET /hello[/{name}]'] = UserList::class;

        return [ $routes, $configs ];
    }
}
