<?php

namespace App\Services\Contracts;

interface ApiClientInterface
{
    public function fetchData(string $endpoint, array $params = [], int $limit = 500, ?int $maxPages = null): array;
}
