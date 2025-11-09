# Выгрузка данных с wb

## Локальный запуск:
```bash
cp .env.example .env
# Настройте .env для Docker
```

### 1.1 Разработка с cron (когда нужно тестировать планировщик)
```
docker compose --profile cron up app-webserver cron
docker compose run --rm composer install
docker compose run --rm artisan migrate
```

### 1.2 Разработка без cron (по умолчанию)
```
docker compose up app-webserver
docker compose run --rm composer install
docker compose run --rm artisan migrate
```

## Тестирование:

### 1. Тестирование команд управления

1. Сначала запустим миграции и сидеры

```shell
# Очищаем базу и запускаем все миграции с сидерами
docker compose run --rm artisan migrate:fresh --seed
```

2. Тестируем команду добавления компании
```shell
# Простой вызов (будет запрашивать email и description)
docker compose run --rm artisan company:add "Тестовая компания ООО"

# С полными параметрами
docker compose run --rm artisan company:add "ООО Ромашка" --email="contact@romashka.ru" --description="Продажа цветов и растений"

# Еще одна компания
docker compose run --rm artisan company:add "ИП Иванов" --email="ivanov@mail.ru" --description="Индивидуальный предприниматель"
```

3. Тестируем команду добавления типа токена
```shell
# Пытаемся создать дубликат типа токена
docker compose run --rm artisan token-type:add "Bearer Duplicate" bearer
```

4. Тестируем команду добавления токена
```shell
# 1. Несуществующий аккаунт
docker compose run --rm artisan token:add 9999 1 "ozon_api_key_67890" --name="API Key Ozon"

# 2. Неподдерживаемый тип токена для сервиса
docker compose run --rm artisan token:add 1 999 "test_token" --expires-at="2024-12-31"

# 3. Невалидные credentials для типа токена
docker compose run --rm artisan token:add 1 3 "invalid_credentials_without_colon"
```

5. Тестируем команду добавления аккаунта(Сначала узнаем ID компаний и сервисов )
```shell
#  Пытаемся создать аккаунт который уже существует
docker compose run --rm artisan account:add 1 1 "Основной WB магазин"

# Пытаемся создать аккаунт с несуществующей компанией
docker compose run --rm artisan account:add 9999 1 "Invalid Account"
```

### 2. Тестирование команд выгрузки данных

1. Общие тесты для всех fetch команд
```shell
# 1. Несуществующий аккаунт
docker compose run --rm artisan wb:fetch-orders 9999
docker compose run --rm artisan wb:fetch-sales 9999
docker compose run --rm artisan wb:fetch-stocks 9999
docker compose run --rm artisan wb:fetch-incomes 9999

# 2. Аккаунт без токена (создадим такой)
docker compose run --rm artisan company:add "No Token Company"
docker compose run --rm artisan account:add [company_id] 1 "Account Without Token"
docker compose run --rm artisan wb:fetch-orders [account_id]
```


## Примеры команд:
**Выгрузка всех таблиц:** `docker compose run --rm artisan wb:fetch-all --limit=10 --dateFrom=2025-10-21 --dateTo=2025-10-22 -v`

**Выгрузка incomes:** `docker compose run --rm artisan wb:fetch-incomes --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`

**Выгрузка orders:** `docker compose run --rm artisan wb:fetch-orders --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=10`

**Выгрузка stocks:** `docker compose run --rm artisan wb:fetch-stocks --limit=10`

**Выгрузка sales:** `docker compose run --rm artisan wb:fetch-sales --dateFrom=2025-10-21 --dateTo=2025-10-22 --limit=500`

**Команда для мониторинга и диагностики процесса синхронизации данных:** `docker compose run --rm artisan sync:status`
