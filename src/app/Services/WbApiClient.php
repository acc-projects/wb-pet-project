<?php

namespace App\Services;

use App\Services\Contracts\ApiClientInterface;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WbApiClient implements ApiClientInterface
{
    private string $baseUrl;
    private ?string $token;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->token = null;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function fetchData(string $endpoint, array $params = [], int $limit = 500, ?int $maxPages = null): array
    {
        if (!$this->token) {
            throw new Exception('Token not set for API client');
        }

        $page = 1;
        $maxPages = $maxPages ?? 1;
        $allData = [];

        do {
            $data = $this->fetchPageWithRetry($endpoint, $params, $page, $limit);

            if (empty($data)) {
                break;
            }

            $allData = array_merge($allData, $data);
            $page++;

            if ($page > $maxPages) {
                break;
            }

            if (count($data) < $limit) {
                break;
            }

            usleep(200000);

        } while (true);

        return $allData;
    }

    private function fetchPageWithRetry(string $endpoint, array $params, int $page, int $limit): array
    {
        $maxRetries = env('WB_MAX_RETRIES', 5);

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $this->makeApiRequest($endpoint, $params, $page, $limit);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                // Обработка HTTP 429 - Too Many Requests
                if ($response->status() === 429) {
                    if (!$this->handleRateLimit($response, $attempt, $maxRetries)) {
                        break; // Прекращаем попытки после максимального количества retry
                    }
                    continue;
                }

                // Обработка других HTTP ошибок
                if ($response->failed()) {
                    $this->handleHttpError($response, $endpoint, $page);
                    break;
                }

            } catch (Exception $e) {
                $this->handleRequestException($e, $endpoint, $page, $attempt, $maxRetries);

                if ($attempt === $maxRetries) {
                    break;
                }

                usleep(1000 * 1000 * $attempt); // Экспоненциальная задержка
            }
        }

        return [];
    }

    private function makeApiRequest(string $endpoint, array $params, int $page, int $limit): Response
    {
        $url = $this->baseUrl . $endpoint;

        $queryParams = array_merge($params, [
            'page' => $page,
            'limit' => $limit,
            'key' => $this->token,
        ]);

        return Http::timeout(60)
            ->retry(3, 100)
            ->get($url, $queryParams);
    }

    /**
     * Обработка Rate Limiting (429)
     */
    private function handleRateLimit(Response $response, int $attempt, int $maxRetries): bool
    {
        $retryAfter = $response->header('Retry-After');
        $delay = $retryAfter ? $retryAfter * 1000000 : 1000 * 1000 * pow(2, $attempt);

        Log::warning("WB API Rate Limit Hit", [
            'attempt' => $attempt,
            'max_retries' => $maxRetries,
            'retry_after' => $retryAfter,
            'delay_ms' => $delay / 1000
        ]);

        if ($attempt >= $maxRetries) {
            return false;
        }

        usleep($delay);
        return true;
    }

    /**
     * Обработка HTTP ошибок (кроме 429)
     */
    private function handleHttpError(Response $response, string $endpoint, int $page): void
    {
        Log::error('WB API HTTP Error', [
            'endpoint' => $endpoint,
            'page' => $page,
            'status' => $response->status(),
            'response' => $response->body()
        ]);
    }

    /**
     * Обработка исключений запроса
     */
    private function handleRequestException(Exception $e, string $endpoint, int $page, int $attempt, int $maxRetries): void
    {
        Log::error('WB API Request Exception', [
            'endpoint' => $endpoint,
            'page' => $page,
            'attempt' => $attempt,
            'max_retries' => $maxRetries,
            'exception' => $e->getMessage()
        ]);
    }
}
