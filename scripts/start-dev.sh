#!/bin/bash
# Запуск проекта для локальной разработки (без Docker)
# Требования: PHP, Composer, Node.js, npm

cd "$(dirname "$0")/.."

echo "=== 1. Проверка БД и миграции ==="
(cd backend && php artisan migrate --seed --force) 2>/dev/null || true

echo ""
echo "=== 2. Запуск Laravel API (порт 8080) ==="
cd backend && php artisan serve --port=8080 &
LARAVEL_PID=$!

echo ""
echo "=== 3. Запуск Vue frontend (порт 5173) ==="
cd frontend && npm run dev &
VITE_PID=$!

echo ""
echo "=========================================="
echo "Проект запущен!"
echo "  Frontend: http://localhost:5173/"
echo "  API:      http://localhost:8080/api/"
echo "=========================================="
echo "Нажмите Ctrl+C для остановки"
wait $LARAVEL_PID $VITE_PID
