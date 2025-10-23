<?php

namespace App\Transformers;

use App\Transformers\Contracts\DataTransformerInterface;
use Carbon\Carbon;

class StockTransformer implements DataTransformerInterface
{
    public function transform(array $apiData): array
    {
        return [
            'stock_date' => Carbon::parse($apiData['date']),
            'warehouse_name' => $apiData['warehouse_name'],
            'nm_id' => $apiData['nm_id'],
            'quantity' => (int) $apiData['quantity'],
            'in_way_to_client' => (int) $apiData['in_way_to_client'],
            'in_way_from_client' => (int) $apiData['in_way_from_client'],
            'price' => (float) $apiData['price'],
            'discount_percent' => (int) $apiData['discount'],
            'supplier_article' => $apiData['supplier_article'],
            'product_category' => $apiData['category'],
            'brand' => $apiData['brand'],
            'raw_data' => $apiData
        ];
    }
}
