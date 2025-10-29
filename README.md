# Выгрузка данных с wb

## Локальный запуск:
```bash
cp .env.example .env
# Настройте .env для Docker
docker compose up webserver
docker compose run --rm artisan migrate
```

## Примеры команд:
**Выгрузка всех таблиц:** `docker compose run --rm artisan wb:fetch-all --limit=10 --dateFrom=2025-10-21 --dateTo=2025-10-22`

**Выгрузка incomes:** `docker compose run --rm artisan wb:fetch-incomes --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`

**Выгрузка orders:** `docker compose run --rm artisan wb:fetch-orders --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`

**Выгрузка stocks:** `docker compose run --rm artisan wb:fetch-stocks --limit=10`

**Выгрузка sales:** `docker compose run --rm artisan wb:fetch-sales --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=500`
