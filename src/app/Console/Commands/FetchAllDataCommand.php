<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Factories\WbApiServiceFactory;
use App\Enums\ApiEntity;

class FetchAllDataCommand extends Command
{
    protected $signature = 'wb:fetch-all
                            {--dateFrom= : Start date (Y-m-d)}
                            {--dateTo= : End date (Y-m-d)}
                            {--limit=500 : Limit per page (max 500)}
                            {--max-pages= : Maximum number of pages to fetch}';

    protected $description = 'Fetch and save all data from WB API';

    public function handle(): void
    {
        $entities = [
            ApiEntity::ORDERS,
            ApiEntity::SALES,
            ApiEntity::STOCKS,
            ApiEntity::INCOMES,
        ];

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


        $totalSaved = 0;

        foreach ($entities as $entity) {
            $this->info("Fetching {$entity->getName()}...");

            $service = WbApiServiceFactory::create($entity);

            // Для stocks используем только текущую дату
            $entityParams = $params;
            if ($entity === ApiEntity::STOCKS) {
                $entityParams['dateFrom'] = now()->format('Y-m-d');
            }


            $savedCount = $service->fetchAndSaveData($entityParams);
            $totalSaved += $savedCount;

            $this->info("Saved {$savedCount} {$entity->getName()} records");
        }

        $this->info("✅ Successfully saved {$totalSaved} total records!");
    }
}
