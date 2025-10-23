<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Factories\WbApiServiceFactory;

class FetchIncomesCommand extends Command
{
    protected $signature = 'wb:fetch-incomes
                            {--dateFrom= : Start date (Y-m-d)}
                            {--dateTo= : End date (Y-m-d)}
                            {--limit=500 : Limit per page (max 500)}
                            {--max-pages= : Maximum number of pages to fetch}';

    protected $description = 'Fetch and save incomes from WB API';

    public function handle(): void
    {
        $this->info('Starting incomes fetch...');

        $service = WbApiServiceFactory::createForIncomes();

        $params = [];
        if ($this->option('dateFrom')) {
            $params['dateFrom'] = $this->option('dateFrom');
        }
        if ($this->option('dateTo')) {
            $params['dateTo'] = $this->option('dateTo');
        }

        if ($this->option('max-pages')) {
            $params['maxPages'] = $this->option('max-pages');
        }

        if ($this->option('limit')) {
            $params['limit'] = $this->option('limit');
        }

        $savedCount = $service->fetchAndSaveData($params);

        $this->info("Successfully saved {$savedCount} incomes!");
    }
}
