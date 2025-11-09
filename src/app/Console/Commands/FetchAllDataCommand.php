<?php

namespace App\Console\Commands;

use App\Enums\ApiEntity;
use App\Models\Account;
use App\Services\WbApiAccountService;
use App\Services\WbApiClient;
use App\Services\WbApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchAllDataCommand extends Command
{
    protected $signature = 'wb:fetch-all
                            {account-id : Account ID for data fetching (required)}
                            {--dateFrom= : Start date (Y-m-d)}
                            {--dateTo= : End date (Y-m-d)}
                            {--limit=500 : Limit per page (max 500)}
                            {--max-pages=1 : Maximum number of pages to fetch}';

    protected $description = 'Fetch and save all data from WB API';

    /**
     * Основной метод обработки команды
     */
    public function handle(WbApiAccountService $accountService): void
    {
        $startTime = microtime(true);
        $accountId = $this->argument('account-id');
        $this->info('🚀 Starting WB data sync for account ID: ' . $accountId . ' at ' . now()->toDateTimeString());

        // Детальная отладочная информация при включенном verbose режиме
        if ($this->option('verbose')) {
            $this->info('📊 Debug mode enabled');
            $this->info('⚙️ Options: ' . json_encode([
                    'account-id' => $accountId,
                    'dateFrom' => $this->option('dateFrom'),
                    'dateTo' => $this->option('dateTo'),
                    'limit' => $this->option('limit'),
                    'max-pages' => $this->option('max-pages')
                ]));
        }

        // Ищем аккаунт в базе данных вместе со связанным API сервисом
        $account = Account::with(['apiService'])->find($accountId);

        if (!$account) {
            $this->error("❌ Account with ID {$accountId} not found!");
            return;
        }

        $this->info("✅ Found account: {$account->name}");

        // Получаем токен доступа для этого аккаунта
        $token = $accountService->getAccountToken($account);
        if (!$token) {
            $this->error("❌ No active bearer token found for account: {$account->name}");
            return;
        }

        $entities = [
            ApiEntity::ORDERS,
            ApiEntity::SALES,
            ApiEntity::STOCKS,
            ApiEntity::INCOMES,
        ];

        // Получаем параметры из командной строки
        $dateFrom = $this->option('dateFrom');
        $dateTo = $this->option('dateTo');
        $limit = (int) $this->option('limit');
        $maxPages = $this->option('max-pages') ? (int) $this->option('max-pages') : null;

        $totalSaved = 0;
        $results = [];

        foreach ($entities as $entity) {
            $entityStart = microtime(true);
            $this->info("\n📥 Fetching {$entity->value} for account: {$account->name}...");

            try {
                // Создаем API клиент с базовым URL из настроек аккаунта
                $apiClient = new WbApiClient($account->apiService->base_url);
                $apiClient->setToken($token);

                // Создаем сервис для работы с конкретной сущностью
                $apiService = new WbApiService(
                    $apiClient,
                    app(\App\Services\Contracts\DataProcessorInterface::class),
                    $entity
                );

                // Подготавливаем параметры запроса с учетом типа сущности
                $params = $this->prepareEntityParams($entity, $accountService, $account, $dateFrom, $dateTo);

                $params['limit'] = $limit;
                if ($maxPages) {
                    $params['maxPages'] = $maxPages;
                }

                $this->info("📅 " . $this->getDateInfo($params, $entity));
                $this->info("📄 Limit: {$limit}, Max pages: " . ($maxPages ?? 'No limit'));

                $savedCount = $apiService->fetchAndSaveDataForAccount($account->id, $params);

                // Обновляем дату последней синхронизации для этой сущности
                $accountService->updateLastSyncDate($account->id, $entity);

                // Сохраняем результаты для итоговой статистики
                $results[$entity->value] = $savedCount;
                $totalSaved += $savedCount;

                $entityTime = round(microtime(true) - $entityStart, 2);
                $this->info("✅ Saved {$savedCount} {$entity->value} records in {$entityTime}s");

                $accountService->logSyncOperation($account->id, $entity, $savedCount);

            } catch (\Exception $e) {
                $errorMsg = "❌ Failed to fetch {$entity->value} for account {$account->name}: " . $e->getMessage();
                $this->error($errorMsg);
                Log::error($errorMsg);
                $results[$entity->value] = 'FAILED: ' . $e->getMessage();

                $accountService->logSyncOperation($account->id, $entity, 0, $e->getMessage());
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);

        // 🆕 Детальная статистика выполнения
        $this->info("\n🎉 ======= SYNC COMPLETE =======");
        $this->info("👤 Account: {$account->name} (ID: {$account->id})");
        $this->info("⏱️  Total execution time: {$executionTime}s");
        $this->info("📊 Total records saved: {$totalSaved}");
        $this->info("📈 Results by entity:");
        foreach ($results as $entity => $count) {
            $this->info("   - {$entity}: {$count}");
        }
        $this->info("💾 Memory peak: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB");
    }

    /**
     * Подготавливает параметры запроса для конкретного типа сущности
     */
    private function prepareEntityParams(
        ApiEntity $entity,
        WbApiAccountService $accountService,
        Account $account,
        ?string $dateFrom,
        ?string $dateTo
    ): array {
        // Получаем базовые параметры из сервиса (учитывает last_sync_date)
        $params = $accountService->getDateParams($account->id, $entity);

        // Переопределяем dateFrom если указан в команде
        if ($dateFrom) {
            $params['dateFrom'] = $dateFrom;
        }

        if ($entity === ApiEntity::STOCKS) {
            // Для stocks используем только текущую дату если dateFrom не указан явно
            $params['dateFrom'] = now()->format('Y-m-d');
        } else {
            // Для других сущностей используем dateTo если указан
            if ($dateTo) {
                $params['dateTo'] = $dateTo;
            }
        }

        return $params;
    }

    /**
     * Форматирует информацию о датах для отображения в консоли
     */
    private function getDateInfo(array $params, ApiEntity $entity): string
    {
        // Для stocks показываем только дату
        if ($entity === ApiEntity::STOCKS) {
            return "Date: {$params['dateFrom']}";
        }

        // Для остальных сущностей показываем диапазон дат
        $info = "Date range: {$params['dateFrom']}";
        if (isset($params['dateTo'])) {
            $info .= " to {$params['dateTo']}";
        }

        return $info;
    }
}
