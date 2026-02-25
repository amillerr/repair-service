#!/usr/bin/env bash
# Локальный запуск: контейнеры, миграции, сиды, тесты.
# Использование: ./scripts/local_test.sh

set -euo pipefail

cd "$(dirname "$0")/.."

# docker compose (v2) или docker-compose (v1)
if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  DOCKER_COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DOCKER_COMPOSE="docker-compose"
else
  echo "Ошибка: нужен Docker и docker compose или docker-compose"
  exit 1
fi

echo "=== 1. .env ==="
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Создан .env из .env.example"
fi

echo "=== 2. Запуск контейнеров ==="
$DOCKER_COMPOSE up -d --build

echo "=== 3. Ожидание PostgreSQL ==="
sleep 8

echo "=== 4. APP_KEY (если пусто) ==="
$DOCKER_COMPOSE exec -T backend php artisan key:generate --force 2>/dev/null || true

echo "=== 5. migrate:fresh --seed ==="
$DOCKER_COMPOSE exec -T backend php artisan migrate:fresh --seed --force

echo "=== 6. Тесты ==="
$DOCKER_COMPOSE exec -T backend php artisan test

echo ""
echo "=== Готово ==="
echo "Frontend: http://localhost:5173"
echo "Backend:  http://localhost:8000"
echo "Health:   curl http://localhost:8000/api/health"
echo "Логин:    curl -X POST http://localhost:8000/api/login -H 'Content-Type: application/json' -d '{\"name\": \"Алексей Смирнов\"}'"
