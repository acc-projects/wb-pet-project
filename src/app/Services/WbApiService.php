<?php

namespace App\Services;

use App\Enums\ApiEntity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

readonly class WbApiService
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private ApiEntity $entity
    ) {}

    public function fetchAndSaveData(array $params = []): int
    {
        $endpoint = $this->entity->endpoint();

        $limit = min(500, (int)($params['limit'] ?? 500));

        $maxPages = $params['maxPages'] ?? null;
        unset($params['limit'], $params['maxPages']);

        $apiData = $this->fetchAllData($endpoint, $params, $limit, $maxPages);

        $transformerClass = $this->entity->transformerClass();
        $transformer = app($transformerClass);

        $modelClass = $this->entity->modelClass();
        $uniqueKeys = $this->entity->uniqueKeys();

        $savedCount = 0;
        $processedKeys = [];
        $actualSaves = 0;
        $duplicatesInBatch = 0;
        foreach ($apiData as $item) {
            try {
                $transformedData = $transformer->transform($item);

                $uniqueConditions = [];
                foreach ($uniqueKeys as $key) {
                    if (!array_key_exists($key, $transformedData)) {
                        throw new \InvalidArgumentException("Unique key '{$key}' not found");
                    }
                    $uniqueConditions[$key] = $transformedData[$key];
                }

                // Проверка дубликатов в текущем батче
                $compositeKey = md5(serialize($uniqueConditions));
                if (in_array($compositeKey, $processedKeys)) {
                    $duplicatesInBatch++;
                    Log::debug("Skipping duplicate in batch", $uniqueConditions);
                    continue;
                }
                $processedKeys[] = $compositeKey;

                // Сохранение
                $model = $modelClass::updateOrCreate($uniqueConditions, $transformedData);

                if ($model->wasRecentlyCreated) {
                    $savedCount++;
                    $actualSaves++;
                } else {
                    $actualSaves++; // считаем обновления существующих
                }

            } catch (\Exception $e) {
                Log::error('Error saving data', [
                    'entity' => $this->entity->value,
                    'error' => $e->getMessage(),
                    'data' => $item
                ]);
            }
        }
        Log::info("Data save statistics", [
            'entity' => $this->entity->value,
            'total_processed' => count($apiData),
            'duplicates_in_batch' => $duplicatesInBatch,
            'new_records' => $savedCount,
            'updated_records' => $actualSaves - $savedCount,
            'total_saves' => $actualSaves
        ]);

        return $actualSaves; // возвращаем реальное количество операций
    }

    private function fetchAllData(string $endpoint, array $params = [], int $limit = 500, ?int $maxPages = null): array
    {
        $page = 1;
        $maxPages = $maxPages ?? 1;
        $allData = [];

        do {
            $data = $this->fetchPage($endpoint, $params, $page, $limit);

            if (empty($data)) {
                break;
            }

            $allData = array_merge($allData, $data);
            $page++;

            // Ограничение по количеству страниц (для тестирования)
            if ($page > $maxPages) {
                break;
            }

            // Автоматическое определение последней страницы
            if (count($data) < $limit) {
                break;
            }

            usleep(100000);

        } while (true);

        return $allData;
    }

    private function fetchPage(string $endpoint, array $params, int $page, int $limit): array
    {
        $url = $this->baseUrl . $endpoint;

        $queryParams = array_merge($params, [
            'page' => $page,
            'limit' => $limit,
            'key' => $this->token,
        ]);

        try {
            $response = Http::timeout(60)->get($url, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            } else {
                Log::error('API Request Failed', [
                    'url' => $url,
                    'params' => $queryParams,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('API Request Exception', [
                'url' => $url,
                'params' => $queryParams,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getEntity(): ApiEntity
    {
        return $this->entity;
    }
}
