<?php

namespace App\Console\Commands;

use App\Enums\ApiEntity;
use App\Models\Account;
use App\Services\WbApiAccountService;

class FetchStocksCommand extends AbstractFetchCommand
{
    protected $signature = 'wb:fetch-stocks
                            {account-id : Account ID for data fetching (required)}
                            {--limit=500 : Limit per page (max 500)}
                            {--max-pages=1 : Maximum number of pages to fetch}';

    protected $description = 'Fetch stocks data from Wildberries API for a specific account';

    protected function getApiEntity(): ApiEntity
    {
        return ApiEntity::STOCKS;
    }

    /**
     * Переопределение параметров подготовки для stocks (без параметра dateTo, dateFrom)
     */
    protected function prepareFetchParameters(
        Account $account,
        WbApiAccountService $accountService,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $params = [];

        $params['dateFrom'] = now()->format('Y-m-d');

        return $params;
    }

    protected function displayFetchInfo(array $params, int $limit, ?int $maxPage): void
    {
        $this->info("📅 Date: {$params['dateFrom']}");
        $this->info("📄 Limit: {$limit}, maxPage: {$maxPage}");
    }
}
