<?php

namespace Honeybee\FrameworkBinding\Equip\ConfigBag;

use Honeybee\Common\Error\ConfigError;
use Symfony\Component\Yaml\Parser;

class YamlFileLoader implements ConfigBagLoaderInterface
{
    private $yamlParser;

    public function __construct(Parser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    public function load($source)
    {
        if (!is_readable($source)) {
            throw new ConfigError('Config file '.$source.' is not readable.');
        }

        return $this->yamlParser->parse(file_get_contents($source));
    }
}
