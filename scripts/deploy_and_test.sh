#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
PROJECT_DIR="$(pwd)"

# docker-compose для совместимости с сервером (v1) и локально
DOCKER_COMPOSE="docker-compose"
if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  DOCKER_COMPOSE="docker compose"
fi

echo "=== 1. git pull ==="
if [ -z "${SKIP_GIT_PULL:-}" ]; then
  git pull origin develop 2>/dev/null || true
else
  echo "Skipping (SKIP_GIT_PULL set)"
fi

echo "=== 2. docker-compose up ==="
$DOCKER_COMPOSE up -d --build

echo "=== 3. Waiting for DB to be ready ==="
sleep 10

echo "=== 4. composer install (внутри контейнера) ==="
$DOCKER_COMPOSE exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

echo "=== 5. migrate:fresh --seed ==="
$DOCKER_COMPOSE exec -T app php artisan migrate:fresh --seed --force

echo "=== 6. php artisan test ==="
$DOCKER_COMPOSE exec -T app php artisan test
