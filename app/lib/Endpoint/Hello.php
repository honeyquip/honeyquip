<?php

namespace Honeybee\FrameworkBinding\Equip\Endpoint;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Honeybee\FrameworkBinding\Equip\ConfigBag\ConfigBagInterface;

class Hello implements DomainInterface
{
    private $payload;

    private $configBag;

    public function __construct(PayloadInterface $payload, ConfigBagInterface $configBag)
    {
        $this->payload = $payload;
        $this->configBag = $configBag;
    }

    public function __invoke(array $input)
    {
        $name = 'Cqrs plus Es app boilerplate based on honeybee and equip.';
        if (!empty($input['name'])) {
            $name = 'Hello ' . $input['name'];
        }

        return $this->payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withSetting('template', 'hello')
            ->withOutput([
                'name' => $name
            ]);
    }
}
