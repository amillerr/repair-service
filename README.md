# SaaS-сервис «Заявки в ремонтную службу»

Проект на Laravel 12 (PHP 8.5) для управления заявками в ремонтную службу. Стек и архитектура подготовлены для запуска в Docker с PostgreSQL и nginx.

## Стек

- PHP 8.5 (php-fpm)
- Laravel 12
- PostgreSQL 16
- nginx
- Docker / Docker Compose

## Инфраструктура и конфигурация

- `docker-compose.yml`
  - Сервис `app`: контейнер с PHP-FPM и Composer, рабочая директория `/var/www/html`.
  - Сервис `nginx`: проксирование запросов к `app`, корень `/var/www/html/public`.
  - Сервис `db`: PostgreSQL 16, база `repair_service`, пользователь `repair_user`.
- `docker/php-fpm/Dockerfile`
  - Базовый образ `php:8.5-fpm`.
  - Установлены расширения `pdo` и `pdo_pgsql`.
  - Установлен Composer.
- `docker/nginx/nginx.conf`
  - Проксирование PHP-запросов на `app:9000`.
  - `root /var/www/html/public`.
- `.env.example`
  - Настроено подключение к PostgreSQL:
    - `DB_CONNECTION=pgsql`
    - `DB_HOST=db`
    - `DB_PORT=5432`
    - `DB_DATABASE=repair_service`
    - `DB_USERNAME=repair_user`
    - `DB_PASSWORD=repair_password`
  - `APP_URL=http://localhost:8080`.

## Доменные модели и БД

### Модель `Request`

- Файл: `app/Models/Request.php`.
- Статусы заявки:
  - `new`
  - `assigned`
  - `in_progress`
  - `done`
  - `canceled`
- Поля:
  - `title` — заголовок заявки.
  - `description` — описание заявки.
  - `status` — статус (enum по константам модели).
  - `dispatcher_id` — ссылка на пользователя-диспетчера.
  - `master_id` — ссылка на пользователя-мастера.
- Связи:
  - `dispatcher()` → `User`.
  - `master()` → `User`.

### Миграции

- `2026_02_23_000000_create_requests_table.php`
  - Создаёт таблицу `requests` с полями:
    - `id`
    - `title`
    - `description`
    - `dispatcher_id` (FK → `users`, `nullOnDelete`)
    - `master_id` (FK → `users`, `nullOnDelete`)
    - `status` (enum: `new | assigned | in_progress | done | canceled`, по умолчанию `new`)
    - timestamps.
- `2026_02_23_000001_add_role_to_users_table.php`
  - Добавляет столбец `role` в таблицу `users` (string, по умолчанию `dispatcher`).

## Роли и сиды

- В таблице `users` поле `role`:
  - `dispatcher`
  - `master`
- Сиды:
  - `database/seeders/UserSeeder.php`:
    - 1 диспетчер: `dispatcher@example.com`, роль `dispatcher`.
    - 2 мастера: `master1@example.com`, `master2@example.com`, роль `master`.
  - `database/seeders/DatabaseSeeder.php` вызывает `UserSeeder`.

## Сервисный слой и защита от race condition

### Сервис `RequestService`

- Файл: `app/Services/RequestService.php`.
- Метод `takeInWork(Request $request, User $master)`:
  - Выполняется в транзакции `DB::transaction`.
  - Заявка перечитывается с блокировкой `lockForUpdate()` по первичному ключу.
  - Проверяется существование заявки и то, что её статус `new`.
  - При несоответствии статуса выбрасывается `RuntimeException` (заявка уже взята или закрыта).
  - При успехе:
    - статус меняется на `in_progress`;
    - проставляется `master_id`;
    - сохраняется обновлённая модель.

Таким образом, параллельные запросы «взять в работу» к одной и той же заявке обрабатываются безопасно: вторая транзакция увидит уже изменённый статус под блокировкой и не сможет повторно взять заявку.

## Авторизация (без Laravel Breeze)

- **Таблица `users`:** `id`, `name` (уникальное), `role` (`dispatcher` | `master`), timestamps.
- **Логин по имени:** пароль не используется; API возвращает токен Sanctum.
- **Middleware ролей:** `role:master` и `role:dispatcher` — проверка роли после `auth:sanctum`.

### Маршруты авторизации

- `POST /api/login` — тело `{"name": "Master 1"}` → `{ "token", "user" }`.
- `GET /api/me` — текущий пользователь (требует `Authorization: Bearer <token>`).
- `POST /api/logout` — инвалидация токена.

### Примеры маршрутов с ролями

- `POST /api/requests/{request}/take` — только для пользователей с ролью `master` (`auth:sanctum` + `role:master`). Мастером считается авторизованный пользователь.

Регистрация middleware: в `bootstrap/app.php` задан алиас `role` → `App\Http\Middleware\EnsureRole`. Использование: `->middleware('role:master')` или `->middleware('role:dispatcher')`.

## API

### Маршруты

- Файл: `routes/api.php`.
- Эндпоинты:
  - `GET /api/health` → `HealthController@index`.
  - `POST /api/login` → `AuthController@login`.
  - `GET /api/me`, `POST /api/logout` — в группе `auth:sanctum`.
  - `POST /api/requests/{request}/take` → `RequestController@takeInWork` (auth + `role:master`).

### Контроллеры

- `app/Http/Controllers/Api/HealthController.php`
  - Возвращает JSON:
    - `{ "status": "ok" }`.
- `app/Http/Controllers/Api/RequestController.php`
  - Внедряет `RequestService`.
  - Метод `takeInWork`:
    - Мастер берётся из авторизации (`$request->user()`), проверка роли — в middleware `role:master`.
    - Вызывает `RequestService::takeInWork`.
    - При бизнес-ошибке (заявка уже взята/закрыта) возвращает `409` с текстом ошибки.
    - При успехе возвращает JSON с `id`, `status`, `assigned_to`.

## Фабрики и тесты

- `database/factories/RequestFactory.php`
  - Генерирует заявки со статусом `new`.

### Feature-тесты

- `tests/Feature/HealthEndpointTest.php`
  - Проверяет:
    - `GET /api/health` возвращает `200 OK` и JSON `{"status":"ok"}`.
- `tests/Feature/TakeRequestInWorkTest.php`
  - Использует `RefreshDatabase`.
  - Сценарий 1: мастер успешно берёт новую заявку в работу.
    - Статус меняется на `in_progress`, проставляется `master_id`, проверяется запись в БД.
  - Сценарий 2: вторая попытка взять ту же заявку.
    - Первая попытка успешна.
    - Вторая попытка получает `409 Conflict`, в БД остаётся первый мастер и статус `in_progress`.

## Запуск проекта

### 1. Подготовка окружения

Скопируйте `.env` на основе примера (если Composer-скрипты не сделали это автоматически):

```bash
cp .env.example .env
```

Сгенерируйте ключ приложения (в контейнере `app`):

```bash
docker compose exec app php artisan key:generate
```

### 2. Поднять контейнеры

```bash
docker compose up --build
```

Приложение будет доступно по адресу `http://localhost:8080`.

### 3. Миграции и сиды

```bash
docker compose exec app php artisan migrate --seed
```

### 4. Проверка health-эндпоинта

```bash
curl http://localhost:8080/api/health
```

Ожидаемый ответ:

```json
{ "status": "ok" }
```

### 5. Авторизация (логин по имени, без пароля)

Логин возвращает токен Sanctum. Дальше запросы с заголовком `Authorization: Bearer <token>`.

```bash
# Логин (имя из сидов: Dispatcher, Master 1, Master 2)
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"name": "Master 1"}'
# В ответе: {"token":"...","user":{"id":2,"name":"Master 1","role":"master"}}

# Текущий пользователь
curl http://localhost:8080/api/me -H "Authorization: Bearer <token>"

# Выход
curl -X POST http://localhost:8080/api/logout -H "Authorization: Bearer <token>"
```

### 6. Пример запроса «взять в работу» (только для мастера)

```bash
curl -X POST http://localhost:8080/api/requests/1/take \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json"
```

### 7. Запуск тестов

```bash
docker compose exec app php artisan test
```

Или (если используется скрипт Composer):

```bash
docker compose exec app composer test
```

