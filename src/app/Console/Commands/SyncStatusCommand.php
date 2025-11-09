<?php

namespace App\Console\Commands;

use App\Models\AccountSyncDate;
use Illuminate\Console\Command;

class SyncStatusCommand extends Command
{
    protected $signature = 'sync:status
                            {--account-id= : Specific account ID}
                            {--entity= : Specific entity type}';

    protected $description = 'Show synchronization status for accounts';

    public function handle(): void
    {
        $accountId = $this->option('account-id');
        $entityType = $this->option('entity');

        $query = AccountSyncDate::with(['account', 'account.company', 'account.apiService']);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $syncDates = $query->get();

        if ($syncDates->isEmpty()) {
            $this->info("No sync records found");
            return;
        }

        $headers = ['Company', 'Account ID', 'Account', 'API Service', 'Entity', 'Last Sync'];
        $rows = [];

        foreach ($syncDates as $syncDate) {
            $lastSync = $syncDate->last_sync_date
                ? $syncDate->last_sync_date->format('Y-m-d H:i:s')
                : 'Never';

            $rows[] = [
                $syncDate->account->company->name,
                $syncDate->account->id,
                $syncDate->account->name,
                $syncDate->account->apiService->name,
                $syncDate->entity_type,
                $lastSync
            ];
        }

        $this->table($headers, $rows);
    }
}
