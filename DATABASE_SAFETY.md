# Database Safety Rules (MANDATORY)

These rules are permanent. They exist because a set of in-process diagnostic
scripts (`migrate:fresh` / `db:wipe` / `migrate` run via `$kernel->call()`
against the default connection) wiped the LIVE `avt-cms` database on
2026-08-30. The data was recovered only from a verified backup.

## The rules

1. **Never run destructive Artisan database commands through an in-process
   diagnostic script.**
   No PHP script may bootstrap the app and call
   `migrate:fresh`, `migrate:refresh`, `db:wipe`, `db:seed`, or `schema:drop`
   via `$kernel->call()` / `Artisan::call()`. Use the CLI (`php artisan ...`)
   with an explicitly named test database, or the `mysql` client directly.

2. **Every test database must end in `_test` or `_testing`.**
   `phpunit.xml` pins the suite to `avt_cms_testing`. The test bootstrap
   (`tests/CreatesApplication.php`) aborts with a `RuntimeException` if the
   configured connection points at any database that is not `:memory:` or a
   `*_test` / `*_testing` database. Keep it that way.

3. **Destructive development commands must explicitly name the disposable
   test database.**
   Examples:
   - `php artisan migrate:fresh --database=mysql` with
     `DB_DATABASE=avt_cms_testing` exported for the process.
   - `php artisan migrate --database=mysql --path=... --force` with
     `DB_DATABASE=avt_cms_testing` exported.
   Never run `migrate:fresh`, `db:wipe`, `migrate:refresh`, or `db:seed`
   with the default connection pointing at `avt-cms`.

4. **`avt-cms` must never be used for automated destructive testing.**
   The only database that may be dropped/recreated by tests or throwaway
   scripts is a disposable `*_testing` database. Never `DROP DATABASE avt-cms`
   except as an explicitly approved, manual disaster-recovery step backed by a
   verified dump.

## Working with the real database

- Take a full `mysqldump` of `avt-cms` before any nontrivial maintenance, and
  store it under `project-storage/backups/`. Verify the dump restores cleanly
  into a scratch database before touching live data.
- Show pending migrations with `php artisan migrate:status`; apply with
  `php artisan migrate --force`. Never `migrate:fresh` / `migrate:refresh` /
  `db:wipe` / `db:seed` / `schema:drop` against `avt-cms`.
- Put the application in maintenance mode (`php artisan down`) while mutating
  live state, and confirm availability before and after.