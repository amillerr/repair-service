#!/usr/bin/env bash
# Проверка race condition: два параллельных запроса «взять в работу» одну заявку.
# Один получает 200 OK, второй — 409 Conflict.
#
# Использование: ./scripts/race_test.sh
# Требует: docker compose up, migrate --seed, jq (опционально)

set -euo pipefail

cd "$(dirname "$0")/.."

API="${API_URL:-http://localhost:8000}"
MASTER_NAME="Алексей Смирнов"

echo "=== Race condition test ==="
echo "API: $API"
echo ""

# Логин мастера
echo "1. Логин..."
LOGIN=$(curl -s -X POST "$API/api/login" \
  -H "Content-Type: application/json" \
  -d "{\"name\": \"$MASTER_NAME\"}")

if echo "$LOGIN" | grep -q '"token"'; then
  TOKEN=$(echo "$LOGIN" | jq -r '.token' 2>/dev/null || echo "$LOGIN" | sed -n 's/.*"token":"\([^"]*\)".*/\1/p')
else
  echo "Ошибка логина: $LOGIN"
  exit 1
fi

# Ищем заявку assigned для этого мастера
echo "2. Поиск заявки assigned..."
REQUESTS=$(curl -s "$API/api/master/requests" -H "Authorization: Bearer $TOKEN")
REQUEST_ID=$(echo "$REQUESTS" | jq -r '.data[] | select(.status == "assigned") | .id' 2>/dev/null | head -1)

if [ -z "$REQUEST_ID" ] || [ "$REQUEST_ID" = "null" ]; then
  echo "Нет заявок в статусе assigned. Создайте заявку и назначьте мастера."
  echo "Пример: POST /api/dispatcher/requests/{id}/assign с master_id"
  exit 1
fi

echo "   Найдена заявка ID=$REQUEST_ID"
echo ""

# Два параллельных запроса (body в файл, http_code в отдельный)
echo "3. Два параллельных запроса take..."
curl -s -o /tmp/race_r1.txt -w "%{http_code}" -X POST "$API/api/master/requests/$REQUEST_ID/take" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" > /tmp/race_code1.txt &
PID1=$!

curl -s -o /tmp/race_r2.txt -w "%{http_code}" -X POST "$API/api/master/requests/$REQUEST_ID/take" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" > /tmp/race_code2.txt &
PID2=$!

wait $PID1 $PID2 2>/dev/null || true

R1=$(cat /tmp/race_code1.txt 2>/dev/null || echo "?")
R2=$(cat /tmp/race_code2.txt 2>/dev/null || echo "?")

echo ""
echo "4. Результаты (HTTP-коды):"
echo "   Запрос 1: $R1"
echo "   Запрос 2: $R2"

# Ожидаем: один 200, один 409
if { [ "$R1" = "200" ] && [ "$R2" = "409" ]; } || { [ "$R1" = "409" ] && [ "$R2" = "200" ]; }; then
  echo ""
  echo "Один запрос — 200 OK, второй — 409 Conflict. Race condition обработан корректно."
  exit 0
else
  echo ""
  echo "Проверьте вывод вручную. Ожидается: один 200, один 409."
  exit 1
fi
