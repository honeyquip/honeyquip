<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

interface EntityTypeLoaderInterface
{
    public function loadAggregateRootTypes(CrateInterface $crate);

    public function loadProjectionTypes(CrateInterface $crate);
}
