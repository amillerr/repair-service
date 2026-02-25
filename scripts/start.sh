#!/usr/bin/env bash
# Однокомандный запуск: .env, docker compose, migrate, seed
# Использование: ./scripts/start.sh

set -euo pipefail

cd "$(dirname "$0")/.."

echo "=== 1. Конфигурация ==="
[ -f backend/.env ] || { cp backend/.env.example backend/.env && echo "  Создан backend/.env"; } || true

echo ""
echo "=== 2. Docker Compose ==="
docker compose up -d --build

echo ""
echo "=== 3. Ожидание backend..."
for i in {1..30}; do
  docker compose exec -T backend php artisan --version 2>/dev/null && break
  sleep 2
  [ $i -eq 30 ] && { echo "  Timeout"; exit 1; }
done

echo ""
echo "=== 4. Laravel ==="
docker compose exec -T backend php artisan key:generate --force 2>/dev/null || true
docker compose exec -T backend php artisan migrate --seed --force

echo ""
echo "=========================================="
echo "Готово!"
echo "  Frontend: http://localhost:5173"
echo "  API:      http://localhost:8000"
echo "=========================================="
