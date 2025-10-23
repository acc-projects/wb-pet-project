<?php

namespace App\Enums;

enum ApiEntity: string
{
    case ORDERS = 'orders';
    case SALES = 'sales';
    case STOCKS = 'stocks';
    case INCOMES = 'incomes';

    public function endpoint(): string
    {
        return match($this) {
            self::ORDERS => '/api/orders',
            self::SALES => '/api/sales',
            self::STOCKS => '/api/stocks',
            self::INCOMES => '/api/incomes',
        };
    }

    public function modelClass(): string
    {
        return match($this) {
            self::ORDERS => \App\Models\Order::class,
            self::SALES => \App\Models\Sale::class,
            self::STOCKS => \App\Models\Stock::class,
            self::INCOMES => \App\Models\Income::class,
        };
    }

    public function transformerClass(): string
    {
        return match($this) {
            self::ORDERS => \App\Transformers\OrderTransformer::class,
            self::SALES => \App\Transformers\SaleTransformer::class,
            self::STOCKS => \App\Transformers\StockTransformer::class,
            self::INCOMES => \App\Transformers\IncomeTransformer::class,
        };
    }

    public function uniqueKeys(): array
    {
        return match($this) {
            self::ORDERS => ['g_number'],
            self::SALES => ['sale_id'],
            self::STOCKS => ['stock_date', 'warehouse_name', 'nm_id'],
            self::INCOMES => ['income_id', 'nm_id'],
        };
    }

    public function getName(): string
    {
        return match($this) {
            self::ORDERS => 'Orders',
            self::SALES => 'Sales',
            self::STOCKS => 'Stocks',
            self::INCOMES => 'Incomes',
        };
    }
}
