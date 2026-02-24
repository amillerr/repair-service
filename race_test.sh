#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"
TOKEN="${TOKEN:-}"
REQUEST_ID="${1:-1}"

if [[ -z "$TOKEN" ]]; then
  echo "Usage: TOKEN=<master_api_token> $0 <request_id>"
  exit 1
fi

echo "Sending two parallel requests to:"
echo "  POST ${BASE_URL}/api/master/requests/${REQUEST_ID}/take"

tmp1="$(mktemp)"
tmp2="$(mktemp)"

curl -s -o /dev/null -w "%{http_code}\n" \
  -X POST \
  "${BASE_URL}/api/master/requests/${REQUEST_ID}/take" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" > "${tmp1}" &
pid1=$!

curl -s -o /dev/null -w "%{http_code}\n" \
  -X POST \
  "${BASE_URL}/api/master/requests/${REQUEST_ID}/take" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" > "${tmp2}" &
pid2=$!

wait "${pid1}"
wait "${pid2}"

status1="$(cat "${tmp1}")"
status2="$(cat "${tmp2}")"

rm -f "${tmp1}" "${tmp2}"

echo "Response codes:"
echo "  1) ${status1}"
echo "  2) ${status2}"
echo
echo "Ожидается один 200 и один 409."

