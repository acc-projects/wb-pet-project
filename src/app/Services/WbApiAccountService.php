<?php

namespace App\Services;

use App\Enums\ApiEntity;
use App\Models\Account;
use App\Models\AccountSyncDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WbApiAccountService
{
    public function getDateParams(int $accountId, ApiEntity $entity): array
    {
        $lastSync = $this->getLastSyncDate($accountId, $entity);

        $params = [];

        if ($entity === ApiEntity::STOCKS) {
            $params['dateFrom'] = now()->format('Y-m-d');
        } else {
            $dateFrom = $lastSync ? $lastSync->format('Y-m-d') : now()->subDays(7)->format('Y-m-d');
            $params['dateFrom'] = $dateFrom;
        }

        return $params;
    }

    public function getLastSyncDate(int $accountId, ApiEntity $entity): ?Carbon
    {
        $syncDate = AccountSyncDate::where('account_id', $accountId)
            ->where('entity_type', $entity->value)
            ->first();

        return $syncDate?->last_sync_date;
    }

    public function updateLastSyncDate(int $accountId, ApiEntity $entity, ?Carbon $date = null): bool
    {
        $syncDate = AccountSyncDate::firstOrCreate(
            ['account_id' => $accountId, 'entity_type' => $entity->value],
            ['last_sync_date' => null]
        );

        $syncDate->last_sync_date = $date ?: now();
        return $syncDate->save();
    }

    public function getActiveWbAccounts(): array
    {
        return Account::with(['company', 'apiService', 'activeTokens'])
            ->whereHas('apiService', function($query) {
                $query->where('code', 'wb')->where('is_active', true);
            })
            ->where('is_active', true)
            ->get()
            ->all();
    }

    public function getAccountToken(Account $account): ?string
    {
        $token = $account->activeTokens()
            ->whereHas('tokenType', function($query) {
                $query->where('code', 'bearer');
            })
            ->first();

        return $token?->credentials;
    }

    public function logSyncOperation(int $accountId, ApiEntity $entity, int $recordsProcessed, ?string $error = null): void
    {
        $logContext = [
            'account_id' => $accountId,
            'entity' => $entity->value,
            'records_processed' => $recordsProcessed,
        ];

        if ($error) {
            Log::error("Sync operation failed", $logContext + ['error' => $error]);
        } else {
            Log::info("Sync operation completed", $logContext);
        }
    }
}
