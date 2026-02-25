# SaaS-сервис «Заявки в ремонтную службу»

Веб-приложение для управления заявками в ремонтную службу. Backend на Laravel 12, frontend на Vue 3.

## Стек

- **Backend:** PHP 8.5, Laravel 12, PostgreSQL 16
- **Frontend:** Vue 3, Vite
- **Инфраструктура:** Docker Compose (backend, nginx, postgres, frontend)

---

## Быстрый старт

```bash
./scripts/start.sh
```

Скрипт сам: создаёт `.env`, поднимает Docker, запускает миграции и сиды.

| Сервис   | URL                      |
|----------|--------------------------|
| Frontend | http://localhost:5173    |
| Backend  | http://localhost:8000    |

---

## Тестовые пользователи

После `migrate --seed` в системе есть:

| Имя              | Роль      | Описание                      |
|------------------|-----------|-------------------------------|
| Dispatcher       | Диспетчер | Управление заявками, назначение |
| Алексей Смирнов  | Мастер    | Выполнение заявок            |
| Дмитрий Кузнецов | Мастер    | Выполнение заявок             |

**Логин по имени, без пароля.** API возвращает токен Sanctum.

---

## Роли

### Диспетчер (`dispatcher`)

- Просмотр всех заявок (`GET /api/dispatcher/requests`)
- Список мастеров и клиентов
- Назначение мастера на заявку (`POST /api/dispatcher/requests/{id}/assign`)
- Отмена заявки (`POST /api/dispatcher/requests/{id}/cancel`)

### Мастер (`master`)

- Просмотр назначенных себе заявок (`GET /api/master/requests`)
- Взять заявку в работу (`POST /api/master/requests/{id}/take`)
- Завершить заявку (`POST /api/master/requests/{id}/complete`)

---

## curl-примеры

### Health

```bash
curl http://localhost:8000/api/health
```

```json
{"status":"ok"}
```

### Логин

```bash
# Диспетчер
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"name": "Dispatcher"}'

# Мастер
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"name": "Алексей Смирнов"}'
```

Ответ: `{"token":"...","user":{"id":1,"name":"Dispatcher","role":"dispatcher"}}`

### Текущий пользователь

```bash
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer <TOKEN>"
```

### Создание заявки (любая роль)

```bash
curl -X POST http://localhost:8000/api/requests \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "client_name": "Иван Иванов",
    "phone": "+7 999 123-45-67",
    "address": "ул. Тестовая, 1",
    "problem_text": "Сломалась розетка"
  }'
```

### Диспетчер: список заявок

```bash
curl http://localhost:8000/api/dispatcher/requests \
  -H "Authorization: Bearer <TOKEN>"
```

### Диспетчер: назначить мастера

```bash
curl -X POST http://localhost:8000/api/dispatcher/requests/1/assign \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"master_id": 2}'
```

### Диспетчер: отменить заявку

```bash
curl -X POST http://localhost:8000/api/dispatcher/requests/1/cancel \
  -H "Authorization: Bearer <TOKEN>"
```

### Мастер: список своих заявок

```bash
curl http://localhost:8000/api/master/requests \
  -H "Authorization: Bearer <TOKEN>"
```

### Мастер: взять заявку в работу

```bash
curl -X POST http://localhost:8000/api/master/requests/1/take \
  -H "Authorization: Bearer <TOKEN>"
```

### Мастер: завершить заявку

```bash
curl -X POST http://localhost:8000/api/master/requests/1/complete \
  -H "Authorization: Bearer <TOKEN>"
```

### История заявки (audit log)

```bash
# Диспетчер
curl http://localhost:8000/api/dispatcher/requests/1/audit \
  -H "Authorization: Bearer <TOKEN>"

# Мастер (только свои заявки)
curl http://localhost:8000/api/master/requests/1/audit \
  -H "Authorization: Bearer <TOKEN>"
```

---

## Как проверить race condition

Если два мастера (или два запроса) одновременно пытаются взять одну и ту же заявку в работу, должен победить только один — второй получит `409 Conflict`.

### Вариант 1: два curl-запроса параллельно

1. Взять токен мастера и ID заявки в статусе `assigned`, назначенной этому мастеру:

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"name": "Алексей Смирнов"}' | jq -r '.token')
echo "Token: $TOKEN"
```

2. Запустить два запроса «взять в работу» почти одновременно (подставьте ID заявки, например 2):

```bash
# Оба запроса уходят параллельно
curl -s -o /tmp/r1.txt -w "%{http_code}" -X POST http://localhost:8000/api/master/requests/2/take \
  -H "Authorization: Bearer $TOKEN" &
curl -s -o /tmp/r2.txt -w "%{http_code}" -X POST http://localhost:8000/api/master/requests/2/take \
  -H "Authorization: Bearer $TOKEN" &
wait
echo "Response 1:"; cat /tmp/r1.txt; echo ""
echo "Response 2:"; cat /tmp/r2.txt
```

Один запрос вернёт `200`, второй — `409` (заявка уже взята).

### Вариант 2: автоматический скрипт

```bash
./scripts/race_test.sh
```

Скрипт логинится, находит подходящую заявку и отправляет два параллельных запроса.

---

## Защита от race condition

В `RequestService::masterTake()` используется транзакция с `refresh()`: заявка перечитывается из БД, проверяется статус `assigned` и `assigned_to`. При одновременных запросах второй получает уже обновлённый статус (`in_progress`) и возвращает `409 Conflict`.

---

## Тесты

```bash
docker compose exec backend php artisan test
```

---

## Локальная разработка (без Docker)

```bash
./scripts/start-dev.sh
```

Требует: PHP, Composer, Node.js, PostgreSQL. Backend: порт 8080, Frontend: порт 5173.

---

## Проверка race condition (скрипт)

```bash
chmod +x scripts/race_test.sh
./scripts/race_test.sh
```

Для Docker используйте `API_URL=http://localhost:8000 ./scripts/race_test.sh` (по умолчанию).
