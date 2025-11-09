<?php

namespace App\Services;

use App\Enums\ApiEntity;
use App\Services\Contracts\ApiClientInterface;
use App\Services\Contracts\DataProcessorInterface;

readonly class WbApiService
{
    public function __construct(
        private ApiClientInterface $apiClient,
        private DataProcessorInterface $dataProcessor,
        private ApiEntity $entity
    ) {}

    /**
     * Выполняем выгрузку и сохранение данных
     */
    public function fetchAndSaveDataForAccount(int $accountId, array $params = []): int
    {
        $limit = min(500, (int)($params['limit'] ?? 500));
        $maxPages = $params['maxPages'] ?? 1;
        unset($params['limit'], $params['maxPages']);

        $apiData = $this->apiClient->fetchData(
            $this->entity->endpoint(),
            $params,
            $limit,
            $maxPages
        );

        return $this->dataProcessor->process($this->entity->value, $apiData, $accountId);
    }

    public function getEntity(): ApiEntity
    {
        return $this->entity;
    }
}
