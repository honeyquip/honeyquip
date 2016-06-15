<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList\StructureVersionListReader;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList\StructureVersionListWriter;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\StructureVersionList\StructureVersionListReader as ViewStoreStructureVersionListReader;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\StructureVersionList\StructureVersionListWriter as ViewStoreStructureVersionListWriter;

class DataAccessConfiguration implements ConfigurationInterface
{
    protected static $defaultWriters = [
        '%prefix%::version_list::event_source::writer' => [
            'class' => StructureVersionListWriter::class,
            'connection' => '%prefix%.event_source'
        ],
        '%prefix%::version_list::view_store::writer' => [
            'class' => ViewStoreStructureVersionListWriter::class,
            'connection' => 'honeybee.view_store',
            'settings' => [
                'index' => '%vendor%-%name%',
                'type' => 'version_list'
            ]
        ]
    ];

    protected static $defaultReaders = [
        '%prefix%::version_list::event_source::reader' => [
            'class' => StructureVersionListReader::class,
            'connection' => '%prefix%.event_source'
        ],
        '%prefix%::version_list::view_store::reader' => [
            'class' => ViewStoreStructureVersionListReader::class,
            'connection' => 'honeybee.view_store',
            'settings' => [
                'index' => '%vendor%-%name%',
                'type' => 'version_list'
            ]
        ]
    ];

    protected static $defaultUows = [];

    protected static $defaultFinders = [];

    protected static $defaultQueryServices = [];

    protected $crate;

    public function __construct(CrateInterface $crate)
    {
        $this->crate = $crate;
    }

    public function apply(Injector $injector)
    {
        $searches = [ '%prefix%', '%vendor%', '%name%' ];
        $replacements = [
            $this->crate->getPrefix(),
            StringToolkit::asSnakeCase($this->crate->getVendor()),
            StringToolkit::asSnakeCase($this->crate->getName())
        ];

        $injector->execute(function (array $storageReaders = []) use ($injector) {
            $storageReaders = array_merge($storageReaders, $this->replaceTplMarkers(self::$defaultReaders));
            $injector->defineParam('storageReaders', $storageReaders);
        });
        $injector->execute(function (array $storageWriters = []) use ($injector) {
            $storageWriters = array_merge($storageWriters, $this->replaceTplMarkers(self::$defaultWriters));
            $injector->defineParam('storageWriters', $storageWriters);
        });
        $injector->execute(function (array $finders = []) use ($injector) {
            $finders = array_merge($finders, $this->replaceTplMarkers(self::$defaultFinders));
            $injector->defineParam('finders', $finders);
        });
        $injector->execute(function (array $unitOfWorks = []) use ($injector) {
            $unitOfWorks = array_merge($unitOfWorks, $this->replaceTplMarkers(self::$defaultUows));
            $injector->defineParam('unitOfWorks', $unitOfWorks);
        });
        $injector->execute(function (array $queryServices = []) use ($injector) {
            $queryServices = array_merge($queryServices, $this->replaceTplMarkers(self::$defaultQueryServices));
            $injector->defineParam('queryServices', $queryServices);
        });
    }

    protected function replaceTplMarkers(array $config)
    {
        $searches = [ '%prefix%', '%vendor%', '%name%' ];
        $replacements = [
            $this->crate->getPrefix(),
            StringToolkit::asSnakeCase($this->crate->getVendor()),
            StringToolkit::asSnakeCase($this->crate->getName())
        ];

        $newConfig = [];
        foreach ($config as $key => $value) {
            $key = str_replace($searches, $replacements, $key);
            if (is_array($value)) {
                $newConfig[$key] = $this->replaceTplMarkers($value);
            } else if (is_string($value)) {
                $newConfig[$key] = str_replace($searches, $replacements, $value);
            } else {
                $newConfig[$key] = $value;
            }
        }

        return $newConfig;
    }
}
