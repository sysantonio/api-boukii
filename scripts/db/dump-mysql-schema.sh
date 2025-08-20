#!/usr/bin/env bash
set -euo pipefail
: "${DB_HOST:=127.0.0.1}"
: "${DB_PORT:=3306}"
: "${DB_USER:=root}"
: "${DB_PASS:=root}"
: "${DB_NAME:=boukii_v5}"

OUT="database/schema/mysql-schema.sql"
mkdir -p database/schema

mysqldump \
  --host="$DB_HOST" --port="$DB_PORT" \
  --user="$DB_USER" --password="$DB_PASS" \
  --no-data --routines --events --skip-comments \
  --databases "$DB_NAME" \
  | sed 's/DEFINER[ ]*=[^*]*\*/\*/g' \
  | sed 's/ CHARACTER SET utf8mb4 COLLATE utf8mb4_[^ ]*/ CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci/g' \
  > "$OUT"

echo "Schema dump written to $OUT"
