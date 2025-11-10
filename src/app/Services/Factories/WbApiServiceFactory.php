<?php

namespace App\Services\Factories;

use App\Enums\ApiEntity;
use App\Services\Contracts\ApiClientInterface;
use App\Services\Contracts\DataProcessorInterface;
use App\Services\WbApiService;

class WbApiServiceFactory
{
    public static function createForOrders(): WbApiService
    {
        return new WbApiService(
            app(ApiClientInterface::class),
            app(DataProcessorInterface::class),
            ApiEntity::ORDERS
        );
    }

    public static function createForSales(): WbApiService
    {
        return new WbApiService(
            app(ApiClientInterface::class),
            app(DataProcessorInterface::class),
            ApiEntity::SALES
        );
    }

    public static function createForStocks(): WbApiService
    {
        return new WbApiService(
            app(ApiClientInterface::class),
            app(DataProcessorInterface::class),
            ApiEntity::STOCKS
        );
    }

    public static function createForIncomes(): WbApiService
    {
        return new WbApiService(
            app(ApiClientInterface::class),
            app(DataProcessorInterface::class),
            ApiEntity::INCOMES
        );
    }

    // Универсальный метод
    public static function create(ApiEntity $entity): WbApiService
    {
        return new WbApiService(
            app(ApiClientInterface::class),
            app(DataProcessorInterface::class),
            $entity
        );
    }
}
