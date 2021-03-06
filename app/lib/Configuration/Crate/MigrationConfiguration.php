<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Migration\FileSystemLoader;

class MigrationConfiguration extends CrateConfiguration
{
    protected static $defaultTargets = [
        '%crate_prefix%::migration::event_source' => [
            'active' => true,
            'settings' => [
                'version_list_reader' => '%crate_prefix%::version_list::event_source::reader',
                'version_list_writer' => '%crate_prefix%::version_list::event_source::writer',
                'target_connection' => '%crate_prefix%.event_source'
            ],
            'migration_loader' => [
                'class' => FileSystemLoader::class,
                'settings' => [ 'directory' => '%crate_dir%/migration/couchdb' ]
            ]
        ],
        '%crate_prefix%::migration::view_store' => [
            'active' => true,
            'settings' => [
                'version_list_reader' => '%crate_prefix%::version_list::view_store::reader',
                'version_list_writer' => '%crate_prefix%::version_list::view_store::writer',
                'target_connection' => ' honeybee.view_store',
                'index' => '%vendor%-%package%'
            ],
            'migration_loader' => [
                'class' => FileSystemLoader::class,
                'settings' => [ 'directory' => '%crate_dir%/migration/elasticsearch' ]
            ]
        ]
    ];

    public function apply(Injector $injector)
    {
        $injector->execute(function (array $migrationTargets = []) use ($injector) {
            $migrationTargets = array_merge_recursive(
                $migrationTargets,
                $this->replaceTplMarkers(self::$defaultTargets)
            );
            $injector->defineParam('migrationTargets', $this->builder->build($migrationTargets));
        });
    }

    protected function replaceTplMarkers(array $config)
    {
        $searches = [ '%crate_prefix%', '%vendor%', '%package%', '%crate_dir%' ];
        $replacements = [
            $this->crate->getPrefix(),
            StringToolkit::asSnakeCase($this->crate->getVendor()),
            StringToolkit::asSnakeCase($this->crate->getName()),
            $this->crate->getRootDir()
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
