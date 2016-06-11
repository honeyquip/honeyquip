<?php

namespace Foh\SystemAccount\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;

class Index implements DomainInterface
{
    private $payload;

    public function __construct(PayloadInterface $payload)
    {
        $this->payload = $payload;
    }

    public function __invoke(array $input)
    {
        return $this->payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withSetting('template', 'hello')
            ->withOutput([
                'name' => __METHOD__
            ]);
    }
}
