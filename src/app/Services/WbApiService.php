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

    public function fetchAndSaveData(array $params = []): int
    {
        $limit = min(500, (int)($params['limit'] ?? 500));
        $maxPages = $params['maxPages'] ?? null;
        unset($params['limit'], $params['maxPages']);

        $apiData = $this->apiClient->fetchData(
            $this->entity->endpoint(),
            $params,
            $limit,
            $maxPages
        );

        return $this->dataProcessor->process($this->entity->value, $apiData);
    }

    public function getEntity(): ApiEntity
    {
        return $this->entity;
    }
}
