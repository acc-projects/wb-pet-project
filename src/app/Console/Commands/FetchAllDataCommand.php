<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Factories\WbApiServiceFactory;
use App\Enums\ApiEntity;
use Illuminate\Support\Facades\Log;

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
        $startTime = microtime(true);
        $this->info('🚀 Starting scheduled WB data sync at ' . now()->toDateTimeString());

        // 🆕 Детальная отладочная информация
        if ($this->option('verbose')) {
            $this->info('📊 Debug mode enabled');
            $this->info('⚙️  Options: ' . json_encode([
                    'dateFrom' => $this->option('dateFrom'),
                    'dateTo' => $this->option('dateTo'),
                    'limit' => $this->option('limit'),
                    'max-pages' => $this->option('max-pages')
                ]));
        }

        $entities = [
            ApiEntity::ORDERS,
            ApiEntity::SALES,
            ApiEntity::STOCKS,
            ApiEntity::INCOMES,
        ];

        $params = [];
        if ($this->option('dateFrom')) {
            $params['dateFrom'] = $this->option('dateFrom');
            $this->info("📅 Date From: {$params['dateFrom']}");
        }
        if ($this->option('dateTo')) {
            $params['dateTo'] = $this->option('dateTo');
            $this->info("📅 Date To: {$params['dateTo']}");
        }
        if ($this->option('max-pages')) {
            $params['maxPages'] = $this->option('max-pages');
            $this->info("📄 Max Pages: {$params['maxPages']}");
        }
        if ($this->option('limit')) {
            $params['limit'] = $this->option('limit');
            $this->info("📦 Limit: {$params['limit']}");
        }

        $totalSaved = 0;
        $results = [];

        foreach ($entities as $entity) {
            $entityStart = microtime(true);
            $this->info("\n📥 Fetching {$entity->getName()}...");

            $service = WbApiServiceFactory::create($entity);

            // Для stocks используем только текущую дату
            $entityParams = $params;
            if ($entity === ApiEntity::STOCKS) {
                $entityParams['dateFrom'] = now()->format('Y-m-d');
                $this->info("🔄 Stocks using current date: {$entityParams['dateFrom']}");
            }

            try {
                $savedCount = $service->fetchAndSaveData($entityParams);
                $totalSaved += $savedCount;
                $results[$entity->value] = $savedCount;

                $entityTime = round(microtime(true) - $entityStart, 2);
                $this->info("✅ Saved {$savedCount} {$entity->getName()} records in {$entityTime}s");

            } catch (\Exception $e) {
                $errorMsg = "❌ Failed to fetch {$entity->getName()}: " . $e->getMessage();
                $this->error($errorMsg);
                Log::error($errorMsg);
                $results[$entity->value] = 'FAILED: ' . $e->getMessage();
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);

        // 🆕 Детальная статистика выполнения
        $this->info("\n🎉 ======= SYNC COMPLETE =======");
        $this->info("⏱️  Total execution time: {$executionTime}s");
        $this->info("📊 Total records saved: {$totalSaved}");
        $this->info("📈 Results by entity:");
        foreach ($results as $entity => $count) {
            $this->info("   - {$entity}: {$count}");
        }
        $this->info("💾 Memory peak: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB");
    }
}
