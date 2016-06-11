<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

use Honeybee\FrameworkBinding\Equip\Crate\CrateManifestInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;

class CrateManifestMap extends TypedMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return CrateManifestInterface::CLASS;
    }
}
