<?php

namespace Foh\SystemAccount;

use Foh\SystemAccount\Endpoint\UserDetail;
use Foh\SystemAccount\Endpoint\UserList;
use Foh\SystemAccount\User\Model\Task\CreateUser\CreateUserCommandHandler;
use Foh\SystemAccount\User\Model\Task\ModifyUser\ModifyUserCommandHandler;
use Foh\SystemAccount\User\Model\Task\ProceedUserWorkflow\ProceedUserWorkflowCommandHandler;
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

        $routes['GET /collection'] = UserList::class;
        $routes['GET /collection/{identifier}'] = UserDetail::class;

        return [ $routes, $configs ];
    }
}
