<?php

namespace App\Services\Contracts;

interface DataProcessorInterface
{
    public function process(string $entity, array $apiData): int;
}
