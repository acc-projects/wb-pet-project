<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Factories\WbApiServiceFactory;

class FetchStocksCommand extends Command
{
    protected $signature = 'wb:fetch-stocks
                            {--limit=500 : Limit per page (max 500)}
                            {--max-pages= : Maximum number of pages to fetch}';

    protected $description = 'Fetch and save stocks from WB API';

    public function handle(): void
    {
        $this->info('Starting stocks fetch...');

        $service = WbApiServiceFactory::createForStocks();

        $params = [];

        $params['dateFrom'] = now()->format('Y-m-d');

        if ($this->option('max-pages')) {
            $params['maxPages'] = $this->option('max-pages');
        }

        if ($this->option('limit')) {
            $params['limit'] = $this->option('limit');
        }

        $savedCount = $service->fetchAndSaveData($params);

        $this->info("Successfully saved {$savedCount} stock records!");
    }
}
