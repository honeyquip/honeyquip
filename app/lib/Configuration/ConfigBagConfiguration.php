<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBag;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagLoaderInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\YamlFileLoader;

class ConfigBagConfiguration extends Configuration
{
    public function apply(Injector $injector)
    {
        $injector->alias(ConfigBagLoaderInterface::class, YamlFileLoader::class);

        $injector
            ->share(ConfigBagInterface::class)
            ->delegate(
                ConfigBagInterface::class,
                function (ConfigBagLoaderInterface $loader) {
                    $configs = $this->detectConfigFiles();
                    $config = $loader->load($configs['core']);
                    $config['app_dir'] = dirname(dirname($configs['core']));
                    $config['core_dir'] = dirname(dirname($configs['core']));
                    if (isset($configs['app'])) {
                        $config = array_merge_recursive($config, $loader->load($configs['app']));
                        $config['app_dir'] = dirname(dirname($configs['app']));
                    }

                    return new ConfigBag($this->builder->build($this->interpolateConfigValues($config)));
                }
            );
    }

    protected function detectConfigFiles()
    {
        $honeyquipDir = dirname(dirname(__DIR__));
        $configs = [ 'core' => $honeyquipDir.'/config/config.yml' ];
        $vendorDir = dirname(dirname(dirname($honeyquipDir)));
        if (basename($vendorDir === 'vendor')) {
            $configs['app'] = dirname($vendorDir).'/app/config.yml';
        }

        return $configs;
    }

    protected function interpolateConfigValues(array $config, array $globalConf = null)
    {
        $globalConf = $globalConf ?: $config;
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->interpolateConfigValues($value, $globalConf);
            } else if (is_string($value)) {
                $config[$key] = $this->interpolateDirectives($globalConf, $value);
            }
        }

        return $config;
    }

    protected function interpolateDirectives($globalConf, $value)
    {
        return preg_replace_callback(
            '/(%(.*?)%)/',
            function ($matchedDirective) use ($globalConf) {
                $value = $this->resolveConfigValue($matchedDirective[2], $globalConf);
                if (preg_match_all('/(%(.*?)%)/', $value, $matches)) {
                    return $this->interpolateDirectives($globalConf, $value);
                }
                return $value;
            },
            $value
        );
    }

    protected function resolveConfigValue($key, array $globalConf)
    {
        $pathParts = explode('.', $key);
        $value = &$globalConf;
        do {
            $curKey = array_shift($pathParts);
            $value = &$value[$curKey];
        } while (!empty($pathParts));

        return $value;
    }
}
