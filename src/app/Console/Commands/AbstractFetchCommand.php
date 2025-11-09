<?php

namespace App\Console\Commands;

use App\Enums\ApiEntity;
use App\Models\Account;
use App\Services\Contracts\DataProcessorInterface;
use App\Services\WbApiAccountService;
use App\Services\WbApiClient;
use App\Services\WbApiService;
use Illuminate\Console\Command;

/**
 * Абстрактный базовый класс для всех команд выборки данных
 */
abstract class AbstractFetchCommand extends Command
{
    /**
     * API-сущность, которую обрабатывает эта команда (должна быть определена в дочерних классах)
     */
    abstract protected function getApiEntity(): ApiEntity;

    protected function getSignature(): string
    {
        return sprintf(
            'wb:fetch-%s
            {account-id : Account ID for data fetching (required)}
            {--dateFrom= : Start date (Y-m-d)}
            {--dateTo= : End date (Y-m-d)}
            {--limit=500 : Limit per page (max 500)}
            {--max-pages=1 : Max Pages}',
            $this->getApiEntity()->value
        );
    }

    public function getDescription(): string
    {
        return sprintf(
            'Fetch and save %s from WB API',
            $this->getApiEntity()->value
        );
    }

    public function handle(WbApiAccountService $accountService): void
    {
        $accountId = $this->argument('account-id');
        $limit = (int) $this->option('limit');
        $maxPage = (int) $this->option('max-pages');


        $dateFrom = null;
        $dateTo = null;

        if ($this->getApiEntity()->value !== ApiEntity::STOCKS->value) {
            $dateFrom = $this->option('dateFrom');
            $dateTo = $this->option('dateTo') ? $this->option('dateTo') : now()->format('Y-m-d');
        }

        $this->processAccount($accountId, $accountService, $dateFrom, $dateTo, $limit, $maxPage);
    }

    /**
     * Основной метод обработки аккаунта
     */
    protected function processAccount(
        int $accountId,
        WbApiAccountService $accountService,
        ?string $dateFrom,
        ?string $dateTo,
        int $limit,
        ?int $maxPage
    ): void {
        $this->info("🔍 Processing account ID: {$accountId}");

        $account = $this->findAccount($accountId);
        if (!$account) {
            return;
        }

        $token = $this->getAccountToken($account, $accountService);
        if (!$token) {
            return;
        }


        $this->executeDataFetching($account, $accountService, $token, $dateFrom, $dateTo, $limit, $maxPage);
    }

    /**
     * Ищет аккаунт в базе данных вместе со связанным API сервисом
     */
    protected function findAccount(int $accountId): ?Account
    {
        $account = Account::with(['apiService'])->find($accountId);

        if (!$account) {
            $this->error("❌ Account with ID {$accountId} not found!");
            return null;
        }

        $this->info("✅ Found account: {$account->name}");
        return $account;
    }

    /**
     * Получить токен доступа для этого аккаунта
     */
    protected function getAccountToken(Account $account, WbApiAccountService $accountService): ?string
    {
        $token = $accountService->getAccountToken($account);

        if (!$token) {
            $this->error("❌ No active bearer token found for account: {$account->name}");
            return null;
        }

        return $token;
    }

    /**
     * Выполнить фактическую выборку данных — общая логика с возможностью настройки
     */
    protected function executeDataFetching(
        Account $account,
        WbApiAccountService $accountService,
        string $token,
        ?string $dateFrom,
        ?string $dateTo,
        int $limit,
        ?int $maxPage
    ): void {
        try {
            $apiEntity = $this->getApiEntity();

            // Разрешить дочерним классам настраивать параметры
            $params = $this->prepareFetchParameters($account, $accountService, $dateFrom, $dateTo);

            $params['limit'] = $limit;
            if ($maxPage) {
                $params['maxPage'] = $maxPage;
            }

            $this->displayFetchInfo($params, $limit, $maxPage);

            $apiClient = new WbApiClient($account->apiService->base_url);
            $apiClient->setToken($token);

            $apiService = new WbApiService(
                $apiClient,
                app(DataProcessorInterface::class),
                $apiEntity
            );

            $savedCount = $apiService->fetchAndSaveDataForAccount($account->id, $params);


            $accountService->updateLastSyncDate($account->id, $apiEntity);

            $this->info("✅ Saved {$savedCount} {$apiEntity->value} for account: {$account->name}");

            $accountService->logSyncOperation($account->id, $apiEntity, $savedCount);

        } catch (\Exception $e) {
            $this->handleFetchError($account, $accountService, $e);
        }
    }

    /**
     * Подготовка параметров выборки
     */
    protected function prepareFetchParameters(
        Account $account,
        WbApiAccountService $accountService,
        ?string $dateFrom,
        ?string $dateTo
    ): array {
        // Получаем параметры дат для запроса
        $params = $accountService->getDateParams($account->id, $this->getApiEntity());

        // Если пользователь указал конкретные даты - переопределяем параметры
        if ($dateFrom) {
            $params['dateFrom'] = $dateFrom;
        }
        if ($dateTo) {
            $params['dateTo'] = $dateTo;
        }

        return $params;
    }

    protected function displayFetchInfo(array $params, int $limit, ?int $maxPage): void
    {
        $dateInfo = "📅 Date range: {$params['dateFrom']}";
        if (isset($params['dateTo'])) {
            $dateInfo .= " to {$params['dateTo']}";
        }

        $this->info($dateInfo);
        $this->info("📄 Limit: {$limit}, maxPage: " . $maxPage);
    }

    protected function handleFetchError(Account $account, WbApiAccountService $accountService, \Exception $e): void
    {
        $this->error("❌ Error processing account {$account->name}: " . $e->getMessage());
        $accountService->logSyncOperation($account->id, $this->getApiEntity(), 0, $e->getMessage());
    }
}
