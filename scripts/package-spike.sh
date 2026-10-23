#!/usr/bin/env bash
# Run against a disposable local/demo database; simulation writes attendance rows.
set -u
cd "$(dirname "$0")/.." || exit 1
environment=$(php artisan env --no-ansi) || exit 1
if [[ "$environment" == *production* ]]; then
  echo 'Refusing package simulation in production.' >&2
  exit 1
fi
failed=0
run() {
  "$@" || failed=1
}
run php artisan migrate --no-interaction
run php artisan attendance:simulate --employees=12 --days=30 --no-interaction
run php artisan unicode-pdf:diagnose --no-interaction
run php artisan slug:generate 'জামদানি কটন থ্রি-পিস'
run php artisan ai-hub:configure --show --no-interaction
# Intentionally not replaced by an app-defined command: upstream contract check.
run php artisan admin-panel:doctor --no-interaction
exit "$failed"
