<?php

namespace Foh\SystemAccount\Endpoint;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;

class UserList implements DomainInterface
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
        $limit = 10;
        $query = new Query(new CriteriaList, new CriteriaList, new CriteriaList, 0, $limit);
        $search = $this->queryService->find($query);

        return $this->payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withSetting('template', 'foh.system_account::hello')
            ->withOutput([
                'users' => $search->getResults(),
                'total_count' => $search->getTotalCount(),
                'page' => ceil($search->getOffset() / $limit),
                'pages' => ceil($search->getTotalCount() / $limit)
            ]);
    }
}
