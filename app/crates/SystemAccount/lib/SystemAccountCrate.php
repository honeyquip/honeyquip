<?php

namespace Foh\SystemAccount;

use Auryn\Injector;
use Foh\SystemAccount\Domain\Index;
use Honeybee\FrameworkBinding\Equip\Crate\Crate;

class SystemAccountCrate extends Crate
{
    public function configure(Injector $injector)
    {
        $this->routes = [
            'GET /hello' => Index::class
        ];
    }
}
