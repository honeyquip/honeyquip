<?php

namespace Honeybee\FrameworkBinding\Equip\Console\Command\Crate;

use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Console\Command\Command;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

abstract class CrateCommand extends Command
{
    protected $crateMap;

    public function __construct(ConfigBagInterface $configBag, CrateMap $crateMap)
    {
        parent::__construct($configBag);

        $this->crateMap = $crateMap;
    }

    protected function addAutoloadConfig($fqns, $cratePath)
    {
        $composerFile = $this->configBag->getProjectDir().'/composer.json';
        $composerConfig = json_decode(file_get_contents($composerFile), true);

        if (!isset($composerConfig['autoload']['psr-4'])) {
            $composerConfig['autoload']['psr-4'] = [];
        }
        if (!preg_match('/\\$/', $fqns)) {
            $fqns .= '\\';
        }
        $composerConfig['autoload']['psr-4'][$fqns] = $cratePath;
        (new Filesystem)->dumpFile(
            $composerFile,
            json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function removeAutoloadConfig($namespace)
    {
        $composerFile = $this->configBag->getProjectDir().'/composer.json';
        $composerConfig = json_decode(file_get_contents($composerFile), true);

        if (isset($composerConfig['autoload']['psr-4'])) {
            $autoloads = $composerConfig['autoload']['psr-4'];

            if (isset($autoloads[$namespace])) {
                unset($autoloads[$namespace]);
            }
            $composerConfig['autoload']['psr-4'] = $autoloads;
            (new Filesystem)->dumpFile(
                $composerFile,
                json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    protected function updateCratesConfig(array $crates)
    {
        $cratesFile = $this->configBag->getConfigDir().'/crates.yml';
        (new Filesystem)->dumpFile($cratesFile, sprintf($this->getCratesConfigTemplate(), Yaml::dump($crates)));
    }

    protected function getCratesConfigTemplate()
    {
        return <<<CRATES
#
# list of crates that will be loaded into the app.
---
%s
CRATES;
    }
}
