# Teacher's Day Doctor Reel Generator

Module 1 establishes the Laravel 12 data layer and route foundation for the Teacher's Day campaign. It includes campaign users, doctor reels, templates, settings, status history, activity logging, and Laravel's standard database queue tables.

## Local installation

Requirements: PHP 8.2+, Composer, MySQL, and the PHP extensions required by Laravel. FFmpeg and PHP GD will be required when reel generation is added.

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Create a MySQL database and update the `DB_*` values in `.env`:

```dotenv
QUEUE_CONNECTION=sync
```

Then initialize and run the application:

```bash
php artisan migrate --seed
php artisan admin:create
npm install
npm run build
composer run dev
```

No Supervisor process or queue worker is needed locally. With the `sync` driver, future queued reel jobs execute during the web request while retaining the same Laravel job API used in production.

Admin credentials are stored only in the `users` database table. The interactive `admin:create` command securely asks for the email, name, and password; no admin credential belongs in `.env`.

## Production queue switch

The `jobs`, `job_batches`, and `failed_jobs` tables are already included. On the live server, change only the environment configuration and clear cached config:

```dotenv
QUEUE_CONNECTION=database
```

```bash
php artisan config:clear
php artisan migrate --force
php artisan queue:work database --sleep=3 --tries=2 --timeout=300
```

Run the worker under Supervisor or another process monitor in production. Application logic and job dispatch calls do not change.

## Module 1 routes

- `GET /` - campaign landing route
- `GET /health` - lightweight health response
- `GET /admin/login` - separate admin authentication route

Feature routes are added with their corresponding modules so no route points at an unfinished controller.
