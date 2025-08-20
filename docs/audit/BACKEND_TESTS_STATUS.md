# Backend Tests Status

Currently, CI runs a lightweight smoke suite to verify core tables exist and basic Laravel bootstrapping works.

## Current Gate
- `Ci` PHPUnit testsuite runs only `tests/Feature/CiSmokeTest.php`.
- Database smoke check via tinker ensures `users` table is accessible.

## Pending Work
- Re-enable full backend test suite once factories align and SQLite compatibility issues are resolved.
- Follow-up issues:
  - `fix/backend-tests-align-factories`
  - `fix/backend-tests-sqlite-compat`
