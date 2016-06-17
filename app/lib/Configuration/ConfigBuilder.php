<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

class ConfigBuilder implements ConfigBuilderInterface
{
    protected $config;

    protected $valuePath;

    protected $valuePtr;

    public function __construct(array $defaultConfig = null)
    {
        $this->config = $defaultConfig ?: [];
        $this->valuePtr = &$this->config;
        $this->valuePath = [];
    }

    public function build(array $config)
    {
        return array_replace_recursive($config, $this->config);
    }

    public function valueOf($key)
    {
        return isset($this->valuePtr[$key]) ? $this->valuePtr[$key] : null;
    }

    public function __get($key)
    {
        if (!isset($this->valuePtr[$key])) {
            $this->valuePtr[$key] = [];
        }
        $this->valuePath[] = $key;
        $this->valuePtr = &$this->valuePtr[$key];

        return $this;
    }

    public function offsetExists($key)
    {
        return isset($this->valuePtr[$key]);
    }

    public function offsetGet($key)
    {
        return $this->{$key};
    }

    public function offsetSet($key, $value)
    {
        $this->{$key} = $value;
    }

    public function offsetUnset($key)
    {
        if (isset($this->valuePtr[$key])) {
            unset($this->valuePtr[$key]);
        }
    }

    public function __set($key, $value)
    {
        $this->valuePtr[$key] = $value;
        $this->resetPath();
        $this->rewind();
    }

    public function __call($key, array $args = [])
    {
        if (count($args) !== 0) {
            $this->valuePtr[$key] = $args[0];
        }

        return $this;
    }

    public function popPath()
    {
        $valuePath = $this->valuePath;
        $valuePtr = $this->valuePtr;
        $this->rewind();

        array_pop($valuePath);
        while (!empty($valuePath)) {
            $curPath = array_shift($valuePath);
            $this->{$curPath};
        }

        return $this;
    }

    public function rewind()
    {
        $this->valuePath = [];
        $this->valuePtr = &$this->config;
    }
}
