# Tooling

Dit document beschrijft de toolset die voor het Klantenhulpportaal is gebruikt. Het hoort bij issue #1 (KLA-11723).

## Acceptance criteria

| Criterium | Status | Waar |
| --- | --- | --- |
| Laravel/Vite als ontwikkelomgeving | ✅ | `composer.json`, `package.json`, `vite.config.ts` |
| Applicatie gebouwd met Vue 3 | ✅ | `resources/js`, `package.json` |
| Vue Router voor client-side routing | ✅ | `resources/js/router/index.ts`, `resources/js/domains/*/routes.ts` |
| Store factory, router- en Axios-façade | ✅ | `resources/js/services/store`, `resources/js/router`, `resources/js/services/http` |
| Gekoppeld aan een git repository | ✅ | GitHub: `Oranje-Zal-Overwinnen/klantenhulpportaal` |
| Authenticatie via Sanctum | ✅ | `laravel/sanctum`, `auth:sanctum`, `statefulApi()`, `HasApiTokens` |

## Backend

| Tool | Versie / configuratie | Doel |
| --- | --- | --- |
| PHP | `^8.3` (lokaal 8.6) | Runtime |
| Laravel | `^13.17` | Framework, routing, Eloquent, validatie |
| Laravel Sanctum | `^4.0` | SPA-authenticatie met sessie/cookies |
| Laravel Pint | `^1.27` | PHP code style |
| PHPUnit | `^12.5` | Feature- en unit tests |
| SQLite | standaard via `.env` | Lokale database |
| MailHog | SMTP `127.0.0.1:1025` | E-mail lokaal afvangen |

## Frontend

| Tool | Versie | Doel |
| --- | --- | --- |
| Vue | `^3.5` | UI-componenten |
| Vue Router | `^5.5` | Client-side routing en route guards |
| Axios | `^1.21` | HTTP-verkeer naar de Laravel API |
| TypeScript | `^7.0` | Typeveiligheid in de frontend |
| Vite | `^8.0` | Build tool en dev server |
| Tailwind CSS | `^4.0` | Styling |

## Frontend-architectuur

De frontend is per domein georganiseerd:

```text
resources/js/
├── components/          # Algemene componenten (ConfirmDialog, ErrorMessage, FormError)
├── domains/
│   ├── auth/            # Login, registratie, wachtwoordherstel
│   ├── categories/      # Categoriebeheer
│   ├── tickets/         # Tickets, reacties en notities
│   └── users/           # Gebruikersbeheer
├── router/              # Vue Router en auth/admin guards
└── services/
    ├── error/           # Gedeelde error bag en messages
    ├── http/            # Axios-façade en interceptors
    └── store/           # storeModuleFactory
```

Belangrijke afspraken:

- Elke domeinmap heeft een `store.ts` en `routes.ts`.
- `storeModuleFactory` regelt de CRUD-acties en de genormaliseerde state per id.
- `services/http` is de enige plek waar Axios wordt geconfigureerd.
- Foutmeldingen worden centraal gezet door de Axios-response-interceptor en getoond via `ErrorMessage`/`FormError`.

## Backend-architectuur

```text
app/
├── Http/
│   ├── Controllers/     # API-controllers
│   ├── Requests/        # Validatie
│   └── Resources/       # API-responses
├── Models/              # Eloquent-modellen
├── Policies/            # Autorisatie per model
└── Notifications/       # E-mailnotificaties
```

- Autorisatie verloopt via policies (`TicketPolicy`, `ReplyPolicy`, `NotePolicy`, `CategoryPolicy`, `UserPolicy`).
- Routes zijn beschermd met `auth:sanctum`; de policies bepalen daarna wat een gebruiker mag.
- Meervoudige writes (ticket + categorieën) lopen in een `DB::transaction`.

## Commands

```sh
# Installatie
composer install
npm install

# Database
php artisan migrate --seed

# Developen
php artisan serve
npm run dev

# Testen en formatting
php artisan test
vendor/bin/pint

# Productiebuild
npm run build
```

## Issue #1

De implementatie voldoet aan de criteria van issue #1. Het issue kan worden gesloten zodra dit document is gereviewd.
