# Contributing

Use the PHP/Node versions in README. Work only with fictional demo data. Run Pest, Pint, Larastan level 6, registry parity, Composer validation/audit, frontend build and Blade compilation before proposing changes.

Add a regression test for tenant-boundary changes, financial calculations, stock movements, permission gates, retries and public form validation. Never bypass global scopes without an immediate explicit tenant constraint. Never trust client prices or payment totals.

Update module implementation status when shipping a working subset. Run `php artisan docs:features` after editing the registry. Do not describe planned work as shipped. Keep KNOWN-GAPS.md current and preserve actual package APIs over README marketing claims.

Do not commit `.env`, database files, session data, private uploads or vendor/node_modules. Screenshots must show fictional data and reflect actual screens. Conventional commits are expected in a real Git checkout; this environment's protected `.git` is not a repository.
