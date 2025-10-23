<?php

namespace App\Transformers;

use App\Transformers\Contracts\DataTransformerInterface;
use Carbon\Carbon;

class IncomeTransformer implements DataTransformerInterface
{
    public function transform(array $apiData): array
    {
        return [
            'income_id' => $apiData['income_id'],
            'income_date' => Carbon::parse($apiData['date']),
            'date_close' => Carbon::parse($apiData['date_close']),
            'warehouse_name' => $apiData['warehouse_name'],
            'nm_id' => $apiData['nm_id'],
            'quantity' => (int) $apiData['quantity'],
            'total_price' => (float) $apiData['total_price'],
            'supplier_article' => $apiData['supplier_article'],
            'raw_data' => $apiData
        ];
    }
}
