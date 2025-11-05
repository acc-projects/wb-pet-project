<?php

namespace App\Services;

use App\Enums\ApiEntity;
use App\Services\Contracts\DataProcessorInterface;
use Illuminate\Support\Facades\Log;

class DataProcessor implements DataProcessorInterface
{
    public function process(string $entity, array $apiData): int
    {
        $apiEntity = ApiEntity::from($entity);
        $transformerClass = $apiEntity->transformerClass();
        $transformer = app($transformerClass);

        $modelClass = $apiEntity->modelClass();
        $uniqueKeys = $apiEntity->uniqueKeys();

        $savedCount = 0;
        $processedKeys = [];
        $duplicatesInBatch = 0;

        // 🆕 Отладочная информация
        if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
            echo "🔍 Processing: {$entity} - " . count($apiData) . " records to process\n";
        }

        foreach ($apiData as $item) {
            try {
                $transformedData = $transformer->transform($item);
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
                    'error' => $e->getMessage(),
                    'data' => $item
                ]);
            }
        }

        // 🆕 Детальная статистика
        if (app()->runningInConsole() && in_array('-v', $_SERVER['argv'] ?? [])) {
            echo "📊 {$entity} - Processed: " . count($apiData) .
                ", Saved: {$savedCount}, Duplicates: {$duplicatesInBatch}\n";
        }

        $this->logStatistics($entity, count($apiData), $duplicatesInBatch, $savedCount);

        return $savedCount;
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

    private function logStatistics(string $entity, int $totalProcessed, int $duplicates, int $saved): void
    {
        Log::info("Data processing statistics", [
            'entity' => $entity,
            'total_processed' => $totalProcessed,
            'duplicates_in_batch' => $duplicates,
            'new_records' => $saved
        ]);
    }
}
