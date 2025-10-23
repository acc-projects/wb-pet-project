<?php

namespace App\Transformers\Contracts;

interface DataTransformerInterface
{
    public function transform(array $apiData): array;
}
