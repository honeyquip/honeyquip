<?php

namespace Honeybee\FrameworkBinding\Equip\Crate;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use SplFileInfo;
use Trellis\CodeGen\Parser\Config\ConfigIniParser;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;
use Trellis\CodeGen\Schema\EntityTypeDefinition;
use Workflux\Builder\XmlStateMachineBuilder;

class EntityTypeLoader implements EntityTypeLoaderInterface
{
    public function loadAggregateRootTypes(CrateInterface $crate)
    {
        $typeMap = new AggregateRootTypeMap;
        foreach ($this->detectResourceTypes($crate, '/*/entity_schema/aggregate_root.xml') as $prefix => $type) {
            $typeMap->setItem($prefix, $type);
        }

        return $typeMap;
    }

    public function loadProjectionTypes(CrateInterface $crate)
    {
        $typeMap = new ProjectionTypeMap;
        foreach ($this->detectResourceTypes($crate, '/*/entity_schema/projection/standard.xml') as $prefix => $type) {
            $typeMap->setItem($prefix, $type);
        }

        return $typeMap;
    }

    protected function detectResourceTypes(CrateInterface $crate, $pattern)
    {
        $types = [];
        foreach (glob($crate->getConfigDir().$pattern) as $arSchemaFile) {
            $type = $this->loadEntityType($crate, $arSchemaFile);
            $types[$type->getPrefix()] = $type;
        }

        return $types;
    }

    protected function loadEntityType(CrateInterface $crate, $schemaFile)
    {
        $schemaFile = new SplFileInfo($schemaFile);
        $iniParser = new ConfigIniParser;
        $config = $iniParser->parse(sprintf('%s/%s.ini', $schemaFile->getPath(), $schemaFile->getBasename('.xml')));
        $schema = (new EntityTypeSchemaXmlParser)->parse($schemaFile->getRealPath());
        $entityType = $schema->getEntityTypeDefinition();
        $class = sprintf('%s\\%s%s', $schema->getNamespace(), $entityType->getName(), $config->getTypeSuffix('Type'));
        $workflowFile = sprintf('%s/%s/workflows.xml', $crate->getConfigDir(), $entityType->getName());
        $workflow = $this->loadWorkflow($entityType, $workflowFile);

        return new $class($workflow);
    }

    protected function loadWorkflow(EntityTypeDefinition $entityType, $workflowFile)
    {
        $vendor = $entityType->getOptions()->filterByName('vendor');
        $package = $entityType->getOptions()->filterByName('package');
        if (!$vendor || !$package) {
            throw new ConfigError(
                'Missing vendor- and/or package-option for entity-type: ' . $entityType->getName()
            );
        }
        $builderConfig = [
            'state_machine_definition' => $workflowFile,
            'name' => sprintf(
                '%s_%s_%s_workflow_default',
                strtolower($vendor->getValue()),
                StringToolkit::asSnakeCase($package->getValue()),
                StringToolkit::asSnakeCase($entityType->getName())
            )
        ];

        return (new XmlStateMachineBuilder($builderConfig))->build();
    }
}
