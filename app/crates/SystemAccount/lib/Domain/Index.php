<?php

namespace Foh\SystemAccount\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;

class Index implements DomainInterface
{
    private $payload;

    public function __construct(PayloadInterface $payload, DataAccessServiceInterface $dbal)
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
            ->withSetting('template', 'foh.system_account::hello')
            ->withOutput([
                'name' => "user::".$name
            ]);
    }
}
