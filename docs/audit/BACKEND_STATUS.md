# Backend Status

## Test Coverage
- SQLite smoke suite runs on every pull request.
- Full MySQL suite is gated behind the `run-full-suite` label.

## Migration Debt
- Historical migrations were pruned; the repository boots from a schema dump.
- Incremental migrations after the dump still need reconstruction.

## Convergence Plan
1. Rebuild missing migrations from the schema dump.
2. Gradually re-enable tests and factories.
3. Promote the MySQL full suite once migrations and factories stabilize.
