<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

use Auryn\Injector;
use Psr\Http\Message\ServerRequestInterface;

interface CrateInterface
{
    public function getConfigDir();

    public function getManifest();

    public function getRoutePrefix();

    public function configure(Injector $injector);

    public function dispatch(ServerRequestInterface $request);
}
