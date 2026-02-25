#!/usr/bin/env bash
set -euo pipefail

REMOTE="root@80.90.187.132"
REMOTE_DIR="/opt/repair-service"
# SSH_PSW опционально: export SSH_PSW=xxx для пароля (иначе используйте ключи)
SSH_OPTS="-o StrictHostKeyChecking=no"

echo "=== Uploading project to $REMOTE ==="
if [ -n "${SSH_PSW:-}" ]; then
  sshpass -p "$SSH_PSW" rsync -avz -e "ssh $SSH_OPTS" --exclude '.git' --exclude 'vendor' --exclude 'node_modules' \
    "$(dirname "$0")/../" "$REMOTE:$REMOTE_DIR/"
else
  rsync -avz -e "ssh $SSH_OPTS" --exclude '.git' --exclude 'vendor' --exclude 'node_modules' \
    "$(dirname "$0")/../" "$REMOTE:$REMOTE_DIR/"
fi

echo "=== Running deploy_and_test on remote ==="
if [ -n "${SSH_PSW:-}" ]; then
  sshpass -p "$SSH_PSW" ssh $SSH_OPTS "$REMOTE" "cd $REMOTE_DIR && chmod +x scripts/deploy_and_test.sh && SKIP_GIT_PULL=1 ./scripts/deploy_and_test.sh"
else
  ssh $SSH_OPTS "$REMOTE" "cd $REMOTE_DIR && chmod +x scripts/deploy_and_test.sh && SKIP_GIT_PULL=1 ./scripts/deploy_and_test.sh"
fi
