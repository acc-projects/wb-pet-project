<?php

namespace App\Transformers;

use App\Transformers\Contracts\DataTransformerInterface;
use Carbon\Carbon;

class SaleTransformer implements DataTransformerInterface
{
    public function transform(array $apiData): array
    {
        return [
            'sale_id' => $apiData['sale_id'],
            'sale_date' => Carbon::parse($apiData['date']),
            'total_price' => (float) $apiData['total_price'],
            'for_pay' => (float) $apiData['for_pay'],
            'finished_price' => (float) $apiData['finished_price'],
            'warehouse_name' => $apiData['warehouse_name'],
            'region_name' => $apiData['region_name'],
            'country_name' => $apiData['country_name'],
            'nm_id' => $apiData['nm_id'],
            'supplier_article' => $apiData['supplier_article'],
            'product_category' => $apiData['category'],
            'brand' => $apiData['brand'],
            'raw_data' => $apiData
        ];
    }
}
