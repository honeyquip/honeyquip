<?php

namespace Foh\SystemAccount\Migration\Elasticsearch;

use Honeybee\Infrastructure\Migration\ElasticsearchMigration;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Honeybee\Infrastructure\Migration\MigrationInterface;

class Migration_20140915235807_InitViewStore extends ElasticsearchMigration
{
    protected function up(MigrationTargetInterface $migration_target)
    {
        $this->createIndexIfNotExists($migration_target);
        $this->updateIndexTemplates(
            $migration_target,
            [
                'domain_events' => __DIR__ . DIRECTORY_SEPARATOR . 'domain_events-template.json'
            ]
        );
    }

    protected function down(MigrationTargetInterface $migration_target)
    {
        $this->deleteIndex($migration_target);
    }

    public function getDescription($direction = MigrationInterface::MIGRATE_UP)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            return 'Will create the elasticsearch index for the Foh\SystemAccount\User context\'s view data.';
        }
        return 'Will delete the elasticsearch index for the Foh\SystemAccount\User context.';
    }

    public function isReversible()
    {
        return true;
    }

    protected function getIndexSettingsPath(MigrationTargetInterface $migration_target)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'index-settings.json';
    }

    protected function getTypeMappingPaths(MigrationTargetInterface $migration_target)
    {
    }
}
