#!/usr/bin/env bash
set -euo pipefail

OUTPUT_DIR="database/schema/mysql"
mkdir -p "$OUTPUT_DIR"

MYSQL_PWD="${DB_PASSWORD:-}" mysqldump --no-data \
  -h "${DB_HOST:-127.0.0.1}" \
  -P "${DB_PORT:-3306}" \
  -u "${DB_USERNAME:-root}" \
  "${DB_DATABASE:-}" > "$OUTPUT_DIR/schema.sql"
