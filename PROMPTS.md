# PROMPTS

---

## 23.02 — 22:40–22:50

Используй context7 в текущем проекте.

Создай архитектуру проекта на Laravel 12 (PHP 8.5) для SaaS-сервиса
«Заявки в ремонтную службу».

Стек:
PostgreSQL
Docker Compose
nginx + php-fpm + postgres
запуск через docker compose up
.env
миграции
сиды
health endpoint /api/health
Требования:
роли: dispatcher и master
модель Request со статусами: new | assigned | in_progress | done | canceled
безопасная обработка race condition при действии «take in work»
сервисный слой (RequestService)
сиды: 1 dispatcher, 2 master
минимум 2 feature-теста
корректный запуск проекта через docker compose up

---

## 23.02 — 22:50–22:53

Сопоставь под наши требования:

Роли:
- dispatcher
- master

Модель Request должна содержать:
clientName
phone
address
problemText
status: new | assigned | in_progress | done | canceled
assignedTo (nullable)
createdAt
updatedAt

Требования:
- миграции Laravel
- индексы
- ограничения
- enum для статуса
- foreign key на мастера

Добавь сиды:
- 1 диспетчер
- 2 мастера
- 5 тестовых заявок

Сгенерируй:
- миграции
- модели
- сиды

---

## 23.02 — 22:53–22:59

Реализуй простую авторизацию:

Вариант:
- таблица users
- поля: id, name, role (dispatcher/master)
- логин по имени (без пароля) для упрощения
- middleware проверки роли

Нужно:
- API логин
- middleware для ролей
- примеры маршрутов

Без Laravel Breeze.

---

## 23.02 — 22:59–23:05

Создай API endpoint:

POST /api/requests

Функционал:
- валидация обязательных полей
- статус по умолчанию = new
- вернуть JSON

Использовать:
- FormRequest
- Resource
- корректные HTTP коды

Добавь автотест на успешное создание.

Запушь в feature/project-setup

также создай отдельную ветку develop

---

## 24.02 — 10:50–10:55

Панель диспетчера API
Реализуй API для диспетчера:

GET /api/dispatcher/requests
- фильтр по статусу

POST /api/dispatcher/requests/{id}/assign
- назначение мастера
- статус -> assigned

POST /api/dispatcher/requests/{id}/cancel
- статус -> canceled

Добавь проверки:
- нельзя назначить отменённую заявку
- нельзя назначить done

Добавь feature-тест.

Запушь в feature/dispatcher-api

---

## 24.02 — 10:55–11:05

Панель мастера
Реализуй API для мастера:

GET /api/master/requests
- только назначенные ему

POST /api/master/requests/{id}/take
- assigned -> in_progress

POST /api/master/requests/{id}/complete
- in_progress -> done

Добавь проверки переходов статуса.

Запушь в ветку feature/master-api

---

## 24.02 — 11:05–11:12

Race Condition

Реализуй защиту от race condition для действия "take".

Условие:
Если два параллельных запроса пытаются перевести заявку из assigned в in_progress,
только один должен выполниться успешно.
Второй должен получить 409 Conflict.

Используй:
- транзакцию
- SELECT ... FOR UPDATE
или
- optimistic locking

Добавь тест, который имитирует конкурентные запросы.

Запушь в ветку feature/race-condition-take

---

## 24.02 — 11:12–16:20

race_test.sh
Создай bash-скрипт race_test.sh,
который запускает два параллельных curl запроса
на endpoint take.

Ожидаемый результат:
- один 200
- один 409

Скрипт должен быть исполняемым.

прогони тесты и создай файл с пояснениями что прошло что упало

---

## 25.02 — 12:39–14:30

https://www.figma.com/design/FKwd2mdxndMvw2YcBLKCBb/Repair-service?node-id=1-2&t=CnkgcFPhpQSU4yav-4 релизуй сначала главный лендинг на Vue 3 + Vite.

сделай заглушку серую под изображение

реализуй страницу логина - https://www.figma.com/design/FKwd2mdxndMvw2YcBLKCBb/Repair-service?node-id=2-2&t=kkBc69d95Wa5Caxv-4

реализуй страницу заявки - https://www.figma.com/design/FKwd2mdxndMvw2YcBLKCBb/Repair-service?node-id=5-371&t=kkBc69d95Wa5Caxv-4

реализуй страницу диспетчера - https://www.figma.com/design/FKwd2mdxndMvw2YcBLKCBb/Repair-service?node-id=5-524&t=kkBc69d95Wa5Caxv-4

реализуй страницу мастера - https://www.figma.com/design/FKwd2mdxndMvw2YcBLKCBb/Repair-service?node-id=5-768&t=kkBc69d95Wa5Caxv-4

Запушь фронт в ветку feature/frontend

поправь фронт под бд

сопоставь отображение данных и создание заявки с бд

добавить кнопку выйти

реализовать автоматическое обновление если назначем мастера чтоб у мастера не обновлять страницу ,при новой заявке можем сразу отправлять уведомление в углу экрана
переместить весь бэк в папку backend
Проверка docker-compose:

- backend
- postgres
- nginx
- frontend (node + vite)

Все должно подниматься через docker compose up.

Frontend должен быть доступен на localhost:5173
Backend на localhost:8000

Обнови README.md

Обязательно:

- как запустить (docker compose up)
- тестовые пользователи
- как проверить race condition
- описание ролей
- curl примеры
