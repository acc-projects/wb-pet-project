<?php

namespace App\Services\Factories;

use App\Enums\ApiEntity;
use App\Services\WbApiService;

class WbApiServiceFactory
{
    public static function createForOrders(): WbApiService
    {
        return new WbApiService(
            config('services.wb_api.base_url'),
            config('services.wb_api.token'),
            ApiEntity::ORDERS
        );
    }

    public static function createForSales(): WbApiService
    {
        return new WbApiService(
            config('services.wb_api.base_url'),
            config('services.wb_api.token'),
            ApiEntity::SALES
        );
    }

    public static function createForStocks(): WbApiService
    {
        return new WbApiService(
            config('services.wb_api.base_url'),
            config('services.wb_api.token'),
            ApiEntity::STOCKS
        );
    }

    public static function createForIncomes(): WbApiService
    {
        return new WbApiService(
            config('services.wb_api.base_url'),
            config('services.wb_api.token'),
            ApiEntity::INCOMES
        );
    }

    // Универсальный метод
    public static function create(ApiEntity $entity): WbApiService
    {
        return new WbApiService(
            config('services.wb_api.base_url'),
            config('services.wb_api.token'),
            $entity
        );
    }
}
