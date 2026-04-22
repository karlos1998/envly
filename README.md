# Envly

PL | EN
- [Polski](#polski)
- [English](#english)

## Polski

### O projekcie
Envly to aplikacja do bezpiecznego zarządzania środowiskami (`.env`) per projekt i per środowisko (np. dev/stage/prod).

Główne funkcje:
- zarządzanie wieloma środowiskami dla jednego projektu,
- generowanie i rotacja tokenów dostępowych do pobierania env,
- endpoint API do pobierania env przez `Bearer` token,
- integracja z GitHub (repozytorium, workflow, deploy, secret `ENVLY_TOKEN`),
- logowanie klasyczne + passkeys + GitHub OAuth.

### Wymagania
- PHP `8.4+`
- Composer `2+`
- Node.js `20+`
- npm `10+`
- MySQL `8+` (lub SQLite lokalnie)
- Redis (opcjonalnie lokalnie, zalecany)

### Szybki start (lokalnie, bez Dockera)
1. Sklonuj repo i wejdź do katalogu.
2. Zainstaluj zależności i zbuduj frontend:

```bash
composer install
npm install
```

3. Skopiuj env i wygeneruj klucz:

```bash
cp .env.example .env
php artisan key:generate
```

4. Ustaw dane bazy w `.env` i uruchom migracje:

```bash
php artisan migrate
```

5. Uruchom aplikację (backend + kolejka + Vite):

```bash
composer run dev
```

### Szybki start (Laravel Sail / Docker)

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

### SSR (Inertia)
Projekt wspiera Inertia SSR.

Konfiguracja `.env`:
- `INERTIA_SSR_ENABLED=true`
- `INERTIA_SSR_URL=http://127.0.0.1:13714`

Lokalnie uruchom osobny proces SSR:

```bash
npm run ssr
```

### Kluczowe zmienne `.env`
Uzupełnij co najmniej:
- `APP_NAME`, `APP_ENV`, `APP_URL`
- `FORCE_HTTPS=true` w środowisku za reverse proxy/CDN (np. Cloudflare)
- `TRUSTED_PROXIES=*` (lub konkretne IP/proxy)
- `SESSION_SECURE_COOKIE=true` na produkcji HTTPS
- `DB_*` (połączenie z bazą)

GitHub OAuth (łączenie konta):
- `GITHUB_CLIENT_ID`
- `GITHUB_CLIENT_SECRET`
- `GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"`

Passkeys (WebAuthn):
- `WEBAUTHN_ID` (najczęściej domena aplikacji)
- `WEBAUTHN_ORIGINS` (np. `https://twoja-domena.pl`)

### GitHub App (deploy i sekrety)
Jeśli chcesz używać deploya i aktualizacji `ENVLY_TOKEN` w repo, skonfiguruj GitHub App.

Szczegóły i gotowe presety uprawnień:
- [GITHUB_APP_PERMISSIONS.md](/Users/karolsojka/PRACA/envly/GITHUB_APP_PERMISSIONS.md)

Tworzenie GitHub App:
- [https://github.com/settings/apps](https://github.com/settings/apps)

### Testy i jakość
Uruchom testy:

```bash
php artisan test --compact
```

Formatowanie PHP (Pint):

```bash
vendor/bin/pint --format agent
```

### Deploy (sugestia: Laravel Forge)
Aplikację najprościej utrzymać na Forge.

Minimalne kroki:
1. Stwórz serwer i stronę PHP dla projektu.
2. Ustaw `APP_ENV=production`, `APP_DEBUG=false`, poprawne `APP_URL`.
3. Uzupełnij produkcyjne sekrety (`DB_*`, `GITHUB_*`, itp.).
4. Po deployu uruchom:

```bash
php artisan migrate --force
php artisan optimize
```

5. Włącz worker kolejki (Supervisor) i scheduler (`php artisan schedule:run`).

### Licencja
Projekt jest udostępniany na licencji MIT.

---

## English

### About
Envly is an app for secure `.env` management per project and per environment (for example dev/stage/prod).

Core features:
- multiple environments per project,
- access token generation and rotation,
- API endpoint for env download via `Bearer` token,
- GitHub integration (repository, workflow, deploy, `ENVLY_TOKEN` secret),
- classic auth + passkeys + GitHub OAuth.

### Requirements
- PHP `8.4+`
- Composer `2+`
- Node.js `20+`
- npm `10+`
- MySQL `8+` (or SQLite for local development)
- Redis (optional locally, recommended)

### Quick Start (local, no Docker)
1. Clone the repository and enter the project directory.
2. Install dependencies and build frontend:

```bash
composer install
npm install
```

3. Copy env file and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database credentials in `.env` and run migrations:

```bash
php artisan migrate
```

5. Start app (backend + queue + Vite):

```bash
composer run dev
```

### Quick Start (Laravel Sail / Docker)

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

### SSR (Inertia)
This project supports Inertia SSR.

`.env` configuration:
- `INERTIA_SSR_ENABLED=true`
- `INERTIA_SSR_URL=http://127.0.0.1:13714`

Run SSR in a separate process:

```bash
npm run ssr
```

### Key `.env` Variables
At minimum, configure:
- `APP_NAME`, `APP_ENV`, `APP_URL`
- `FORCE_HTTPS=true` behind reverse proxy/CDN (for example Cloudflare)
- `TRUSTED_PROXIES=*` (or explicit proxy IPs)
- `SESSION_SECURE_COOKIE=true` in HTTPS production
- `DB_*` (database connection)

GitHub OAuth (account connection):
- `GITHUB_CLIENT_ID`
- `GITHUB_CLIENT_SECRET`
- `GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"`

Passkeys (WebAuthn):
- `WEBAUTHN_ID` (typically app domain)
- `WEBAUTHN_ORIGINS` (for example `https://your-domain.com`)

### GitHub App (deploy and secrets)
If you want deploy and `ENVLY_TOKEN` secret sync, configure a GitHub App.

Details and permission presets:
- [GITHUB_APP_PERMISSIONS.md](/Users/karolsojka/PRACA/envly/GITHUB_APP_PERMISSIONS.md)

Create GitHub App:
- [https://github.com/settings/apps](https://github.com/settings/apps)

### Tests and Quality
Run tests:

```bash
php artisan test --compact
```

Format PHP (Pint):

```bash
vendor/bin/pint --format agent
```

### Deployment (suggestion: Laravel Forge)
Laravel Forge is a practical deployment option for this project.

Minimal production steps:
1. Create server and site for the project.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL`.
3. Add production secrets (`DB_*`, `GITHUB_*`, etc.).
4. After deploy run:

```bash
php artisan migrate --force
php artisan optimize
```

5. Configure queue worker (Supervisor) and scheduler (`php artisan schedule:run`).

### License
This project is released under the MIT license.
