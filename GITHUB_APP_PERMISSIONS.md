# GitHub App Permissions (PL / EN)

Create your GitHub App here:
[https://github.com/settings/apps](https://github.com/settings/apps)

---

## PL

### Co jest potrzebne w Envly
Envly używa GitHub API do:
- listy repozytoriów użytkownika,
- listy workflow,
- uruchamiania workflow (`workflow_dispatch`),
- aktualizacji secretu repozytorium `ENVLY_TOKEN`.

### Minimalny zestaw pod aktualny flow
`Repository permissions`:
- `Metadata: Read-only`
- `Actions: Read and write`
- `Secrets: Read and write`

To jest praktyczne minimum dla funkcji deploy + sync secret.

### Opcjonalne (na przyszłość)
- `Contents: Read-only` (gdy chcesz czytać pliki repo przez API)
- `Deployments: Read and write` (gdy chcesz zarządzać deployment status)
- `Variables: Read and write` (gdy chcesz zarządzać Actions/Repo Variables)
- `Workflows: Read and write` (gdy chcesz modyfikować pliki `.github/workflows`)

### Ważne: instalacja App na repozytorium
Najczęstszy błąd 403 `Resource not accessible by integration` oznacza, że App nie ma dostępu do wybranego repo.

Sprawdź:
1. `Install App` / `Configure` dla właściwego ownera (user/org).
2. `Repository access`:
- testowo: `All repositories`,
- docelowo: `Only select repositories` i ręcznie zaznaczone repo.
3. Po zmianie permissions wykonaj ponowną akceptację/aktualizację instalacji.
4. W Envly po zmianach przepnij integrację i wybierz repo + workflow ponownie.

### Bezpieczeństwo
- Zasada najmniejszych uprawnień: zaczynaj od minimum i rozszerzaj tylko gdy funkcja tego wymaga.
- Nie nadawaj `Administration`, jeśli nie jest bezwzględnie potrzebne.

---

## EN

### What Envly needs
Envly uses GitHub API for:
- listing user repositories,
- listing workflows,
- triggering workflow runs (`workflow_dispatch`),
- updating repository secret `ENVLY_TOKEN`.

### Minimal set for current flow
`Repository permissions`:
- `Metadata: Read-only`
- `Actions: Read and write`
- `Secrets: Read and write`

This is the practical minimum for deploy + secret sync.

### Optional (future use)
- `Contents: Read-only` (if you need to read repository files via API)
- `Deployments: Read and write` (if you need deployment status updates)
- `Variables: Read and write` (if you need Actions/Repo Variables management)
- `Workflows: Read and write` (if you need to edit `.github/workflows` files)

### Important: App installation on repository
Most common reason for 403 `Resource not accessible by integration`: the App is not installed for the selected repository.

Check:
1. `Install App` / `Configure` on the correct owner (user/org).
2. `Repository access`:
- for testing: `All repositories`,
- for production: `Only select repositories` and explicitly selected repo.
3. After permission changes, re-approve/update the installation.
4. In Envly, reconnect integration and select repository + workflow again.

### Security
- Follow least privilege: start minimal and expand only when feature requirements demand it.
- Do not grant `Administration` unless absolutely required.
