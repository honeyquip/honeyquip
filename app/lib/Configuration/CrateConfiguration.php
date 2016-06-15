<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Crate\CrateManifest;
use Honeybee\FrameworkBinding\Equip\Crate\CrateManifestMap;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Honeybee\FrameworkBinding\Equip\Crate\EntityTypeLoader;
use Honeybee\FrameworkBinding\Equip\Crate\EntityTypeLoaderInterface;
use ReflectionClass;

class CrateConfiguration implements ConfigurationInterface
{
    private $configBag;

    public function __construct(ConfigBagInterface $configBag)
    {
        $this->configBag = $configBag;
    }

    public function apply(Injector $injector)
    {
        $injector
            ->alias(EntityTypeLoaderInterface::class, EntityTypeLoader::class)
            ->share(EntityTypeLoaderInterface::class);

        $injector
            ->prepare(
                CrateManifestMap::class,
                function (CrateManifestMap $manifests) {
                    foreach ($this->configBag->get('crates') as $crateFqcn => $routePrefix) {
                        $manifest = $this->loadManifest($crateFqcn);
                        $manifests->setItem($routePrefix, $manifest);
                    }
                    return $manifests;
                }
            )
            ->share(CrateManifestMap::class);

        $injector
            ->prepare(
                CrateMap::class,
                function (CrateMap $crateMap) use ($injector){
                    foreach ($injector->make(CrateManifestMap::class) as $routePrefix => $manifest) {
                        $crateClass = $manifest->getClass();
                        $crate = $injector->make($crateClass, [ ':manifest' => $manifest, ':routePrefix' => $routePrefix ]);
                        $crateMap->setItem($manifest->getPrefix(), $crate);
                        $crate->configure($injector);
                    }
                }
            )
            ->share(CrateMap::class);
    }

    protected function loadManifest($crateFqcn)
    {
        $crateReflection = new ReflectionClass($crateFqcn);
        $crateRoot = dirname(dirname($crateReflection->getFileName()));
        $manifestFile = $crateRoot.'/crate.json';
        $parsedJson = json_decode(file_get_contents($manifestFile), true);

        $name = $parsedJson['name'];
        $vendor = $parsedJson['vendor'];
        $description = isset($parsedJson['description']) ? $parsedJson['description'] : '';

        return new CrateManifest($crateRoot, $vendor, $name, $crateFqcn, $description);
    }
}
