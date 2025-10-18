<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

# Styly API

Laravel 12 API prepared for the hackathon demo. The guide below walks judges through bootstrapping the application from a clean machine.

## Prerequisites

- PHP 8.2 or newer with `pdo_sqlite` (or your chosen database driver) enabled
- Composer 2.x
- SQLite (bundled with PHP on macOS/Linux) or another database server
- Git

> macOS "one-liner": `brew install php composer git`

## Hackathon Quick Start

1. Clone the repository and enter the directory:
   ```bash
   git clone <REPOSITORY_URL>
   cd styly/backend
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Bootstrap environment configuration:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Prepare the database (default SQLite file lives at `database/database.sqlite`):
   ```bash
   php artisan migrate:fresh --seed --force
   ```
   > If you prefer MySQL/PostgreSQL, update the `DB_*` variables in `.env` before running the migration.
5. Link the public storage directory so generated images resolve correctly:
   ```bash
   php artisan storage:link
   ```
6. Serve the API locally:
   ```bash
   php artisan serve
   ```
7. The API is now reachable at http://127.0.0.1:8000. Keep the terminal open for the duration of the judging session; press `Ctrl+C` to stop the server.

## Optional Extras

- **Seed demo data:** `php artisan db:seed`
- **Run the queue listener (if jobs are queued):** `php artisan queue:work`
- **Tinker with the app:** `php artisan tinker`

## Testing & Quality Checks

- Execute the Laravel/Pest suite:
  ```bash
  composer run test
  ```
- Apply Pint formatting before commits:
  ```bash
  ./vendor/bin/pint
  ```
- Generate code coverage (optional):
  ```bash
  ./vendor/bin/pest --coverage
  ```

## Troubleshooting

- **Database locked or inconsistent:** Delete `database/database.sqlite` (or drop/reset your DB) and rerun the migration.
- **Port already in use:** Stop other processes on port 8000 or run `php artisan serve --port=8080`.
- **Missing PHP extension:** On macOS with Homebrew PHP, enable it via `php --ini` and update `php.ini` as needed.

Happy hacking!
