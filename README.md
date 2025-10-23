## База данных (railway)

**Хост:** `switchyard.proxy.rlwy.net`

**База:** `railway`

**Пользователь:** `root`

**Пароль:** `cVdErpxmIsugQcKvtIOYCqXNYhLVQfho`

**Порт:** `48063`

### Локальный запуск:
```bash
cp .env.example .env
# Настройте .env для Docker
docker compose up webserver
docker compose run --rm artisan migrate
```

### Примеры команд:
**Выгрузка всех таблиц:** `docker compose run --rm artisan wb:fetch-all --limit=10 --dateFrom=2025-10-21 --dateTo=2025-10-22`
**Выгрузка incomes:** `docker compose run --rm artisan wb:fetch-incomes --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`
**Выгрузка incomes:** `docker compose run --rm artisan wb:fetch-orders --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`
**Выгрузка incomes:** `docker compose run --rm artisan wb:fetch-stocks --limit=10`
