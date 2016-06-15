<?php

namespace Honeybee\FrameworkBinding\Equip\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;
use Honeybee\FrameworkBinding\Equip\Crate\CrateMap;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;

class Hello implements DomainInterface
{
    private $payload;

    public function __construct(PayloadInterface $payload, CrateMap $crateMap, ConnectorServiceInterface $cs)
    {
        $this->payload = $payload;
    }

    public function __invoke(array $input)
    {
        $name = 'world';
        if (!empty($input['name'])) {
            $name = $input['name'];
        }

        return $this->payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withSetting('template', 'hello')
            ->withOutput([
                'name' => $name
            ]);
    }
}
