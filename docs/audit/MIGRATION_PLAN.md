# Migration Plan

The current database is bootstrapped from a MySQL schema dump located at `database/schema/mysql/schema.sql`. Historical Laravel migrations have been removed.

## Goals
- Reconstruct full Laravel migration history.
- Ensure repeatable schema evolution and compatibility with existing data.

## Strategy
1. Extract tables and foreign keys from the schema dump.
2. Regenerate baseline migrations using `laravel-migration-generator`.
3. Commit migrations in batches per bounded context.
4. Validate each batch with the MySQL full test suite.
5. Remove dependency on the schema dump once history is rebuilt.

## Open Questions
- Ordering and idempotency of data backfills.
- Handling of seed data required for CI and smoke tests.
