<?php

namespace Foh\SystemAccount\Endpoint;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;

class UserDetail implements DomainInterface
{
    private $payload;

    private $queryService;

    public function __construct(PayloadInterface $payload, QueryServiceMap $queryServiceMap)
    {
        $this->payload = $payload;
        $this->queryService = $queryServiceMap->getItem('foh.system_account.user::query_service');
    }

    public function __invoke(array $input)
    {
        $search = $this->queryService->findByIdentifier($input['identifier']);

        return $this->payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withSetting('template', 'foh.system_account::user_detail')
            ->withOutput([
                'user' => $search->getFirstResult(),
            ]);
    }
}
