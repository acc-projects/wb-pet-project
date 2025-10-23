<?php

namespace App\Transformers;

use App\Transformers\Contracts\DataTransformerInterface;
use Carbon\Carbon;

class OrderTransformer implements DataTransformerInterface
{
    public function transform(array $apiData): array
    {
        return [
            'g_number' => $apiData['g_number'],
            'order_date' => Carbon::parse($apiData['date']),
            'total_price' => (float) $apiData['total_price'],
            'discount_percent' => (int) $apiData['discount_percent'],
            'warehouse_name' => $apiData['warehouse_name'],
            'region' => $apiData['oblast'],
            'is_cancelled' => $apiData['is_cancel'],
            'cancelled_at' => $apiData['cancel_dt'] ? Carbon::parse($apiData['cancel_dt']) : null,
            'nm_id' => $apiData['nm_id'],
            'supplier_article' => $apiData['supplier_article'],
            'product_category' => $apiData['category'],
            'brand' => $apiData['brand'],
            'raw_data' => $apiData
        ];
    }
}
