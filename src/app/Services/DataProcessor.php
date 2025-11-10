<?php

namespace App\Services;

use App\Enums\ApiEntity;
use App\Services\Contracts\DataProcessorInterface;
use Illuminate\Support\Facades\Log;

class DataProcessor implements DataProcessorInterface
{
    public function process(string $entity, array $apiData, int $accountId): int
    {
        $apiEntity = ApiEntity::from($entity);
        $transformerClass = $apiEntity->transformerClass();
        $transformer = app($transformerClass);

        $modelClass = $apiEntity->modelClass();
        $uniqueKeys = $apiEntity->uniqueKeys();

        $savedCount = 0;
        $processedKeys = [];
        $duplicatesInBatch = 0;

        if ($apiEntity === ApiEntity::STOCKS) {
            $this->clearOldStocks($accountId);
        }

        // 🆕 Отладочная информация
        if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
            echo "🔍 Processing: {$entity} for account {$accountId} - " . count($apiData) . " records\n";
        }

        foreach ($apiData as $item) {
            try {
                $transformedData = $transformer->transform($item);
                $transformedData['account_id'] = $accountId;
                $uniqueConditions = $this->buildUniqueConditions($uniqueKeys, $transformedData);

                // Проверка дубликатов в текущем батче
                $compositeKey = md5(serialize($uniqueConditions));
                if (in_array($compositeKey, $processedKeys)) {
                    $duplicatesInBatch++;
                    if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
                        echo "🔍 Duplicate skipped: " . json_encode($uniqueConditions) . "\n";
                    }
                    continue;
                }
                $processedKeys[] = $compositeKey;

                $model = $modelClass::updateOrCreate($uniqueConditions, $transformedData);
                if ($model->wasRecentlyCreated) {
                    $savedCount++;

                    // 🆕 Отладочная информация о каждом сохранении (только при -vv)
                    if (app()->runningInConsole() && in_array('-vv', $_SERVER['argv'] ?? [])) {
                        echo "✅ Saved new: {$entity} - " . json_encode($uniqueConditions) . "\n";
                    }
                } else {
                    // Информация об обновлении существующей записи
                    if (app()->runningInConsole() && in_array('-vv', $_SERVER['argv'] ?? [])) {
                        echo "🔄 Record updated: {$entity} - " . json_encode($uniqueConditions) . "\n";
                    }
                }

            } catch (\Exception $e) {
                if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
                    echo "❌ Error saving: " . $e->getMessage() . "\n";
                }

                Log::error('Error saving data', [
                    'entity' => $entity,
                    'account_id' => $accountId,
                    'error' => $e->getMessage(),
                    'data' => $item
                ]);
            }
        }

        // 🆕 Детальная статистика
        if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
            echo "📊 {$entity} - Account: {$accountId}, Processed: " . count($apiData) .
                ", Saved: {$savedCount}, Duplicates: {$duplicatesInBatch}\n";
        }

        $this->logStatistics($entity, count($apiData), $duplicatesInBatch, $savedCount, $accountId);

        return $savedCount;
    }

    /**
     * Очистка старых данных по stocks для аккаунта (текущий день)
     */
    private function clearOldStocks(int $accountId): void
    {
        $modelClass = ApiEntity::STOCKS->modelClass();
        $deleted = $modelClass::where('account_id', $accountId)
            ->whereDate('stock_date', now()->format('Y-m-d'))
            ->delete();

        if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
            echo "🗑️  Cleared {$deleted} old stock records for account {$accountId}\n";
        }
    }

    private function buildUniqueConditions(array $uniqueKeys, array $data): array
    {
        $conditions = [];
        foreach ($uniqueKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException("Unique key '{$key}' not found in data");
            }
            $conditions[$key] = $data[$key];
        }
        return $conditions;
    }

    private function logStatistics(string $entity, int $totalProcessed, int $duplicates, int $saved, int $accountId): void
    {
        Log::info("Data processing statistics", [
            'entity' => $entity,
            'account_id' => $accountId,
            'total_processed' => $totalProcessed,
            'duplicates_in_batch' => $duplicates,
            'new_records' => $saved
        ]);
    }
}
