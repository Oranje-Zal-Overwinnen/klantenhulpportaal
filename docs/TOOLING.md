# Tooling

## Stack

```text
PHP / Laravel
Node / Vite
Vue 3 / Vue Router
Axios / TypeScript
Tailwind CSS
SQLite/MySQL Compat
```

## Backend

- Laravel — #1
- Sanctum — #1, #5-#7
- PHPUnit — #1
- Pint — #1
- Faker, factories, seeders — #3
- Policies — #5, #8-#28
- Queued notifications — #7, #11, #22

## Frontend

- Vue 3 — #1
- Vue Router — #1
- Store factory — #1
- Axios façade — #1
- Tailwind CSS — #1
- oxfmt — #1

## Auth

- Sanctum stateful API — #1
- CSRF cookie — #1
- Login/logout — #5, #6
- Password reset — #7
- Registratie + e-mailverificatie — #11
- Policies — #5, #8-#28

## Scripts

```text
composer setup
composer dev
composer test
php artisan test
vendor/bin/pint --test
npm run dev
npm run build
npm run format:check
php artisan migrate --seed
```

## CI

```text
composer validate
composer install
npm ci
npm run format:check
npm run build
php artisan test
vendor/bin/pint --test
```

## Conventies

```text
TypeScript   4 spaces, single quotes, 140 cols, LF
PHP          Pint
Tests        PHPUnit
API          { data: ... } / 422 { errors, message }
Auth         Sanctum stateful + CSRF
```
