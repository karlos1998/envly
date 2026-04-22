# GitHub App Permissions (PL / EN)

Create your GitHub App here:
[https://github.com/settings/apps](https://github.com/settings/apps)

---

## PL

### Cel integracji
Envly łączy środowiska (env) z repozytoriami użytkownika na GitHub i umożliwia automatyczne wdrożenia (deploy) przez Actions/Deployments.

### Rekomendowany zestaw startowy (minimum sensowne)
- `Repository permissions -> Metadata: Read-only`  
  Wymagane jako bazowy odczyt informacji o repozytorium i instalacji.
- `Repository permissions -> Actions: Read and write`  
  Wymagane do uruchamiania workflow (np. `workflow_dispatch`) z poziomu API.
- `Repository permissions -> Contents: Read-only`  
  Wystarcza do odczytu repozytorium. `Write` tylko jeśli aplikacja ma modyfikować pliki/commity.
- `Repository permissions -> Deployments: Read and write`  
  Wymagane, jeśli aplikacja tworzy deploymenty lub aktualizuje ich status.

### Opcjonalne uprawnienia (włączaj tylko gdy potrzebne)
- `Repository permissions -> Secrets: Read and write`  
  Tylko jeśli Envly ma ustawiać sekrety repozytorium dla GitHub Actions.
- `Repository permissions -> Variables: Read and write`  
  Tylko jeśli Envly ma zarządzać zmiennymi Actions/Repository Variables.
- `Repository permissions -> Workflows: Read and write`  
  Tylko jeśli Envly ma edytować pliki workflow w `.github/workflows`.

### Bezpieczna konfiguracja instalacji
- Na start wybieraj instalację na `Only selected repositories`.
- Nie przyznawaj `Administration`, jeśli nie ma konkretnej, wymaganej funkcji.
- Stosuj zasadę najmniejszych uprawnień i rozszerzaj zakres dopiero przy realnej potrzebie funkcjonalnej.

### Proponowany preset v1
`Metadata (RO) + Actions (RW) + Contents (RO) + Deployments (RW)`

---

## EN

### Integration goal
Envly connects environments (env) with user GitHub repositories and enables automated deployments via Actions/Deployments.

### Recommended starter set (practical minimum)
- `Repository permissions -> Metadata: Read-only`  
  Required baseline access for repository and installation metadata.
- `Repository permissions -> Actions: Read and write`  
  Required to trigger workflows (for example `workflow_dispatch`) through the API.
- `Repository permissions -> Contents: Read-only`  
  Enough for repository reads. Use `Write` only if the app must modify files/commits.
- `Repository permissions -> Deployments: Read and write`  
  Required if the app creates deployments or updates deployment statuses.

### Optional permissions (enable only when needed)
- `Repository permissions -> Secrets: Read and write`  
  Only if Envly should manage repository secrets for GitHub Actions.
- `Repository permissions -> Variables: Read and write`  
  Only if Envly should manage Actions/Repository variables.
- `Repository permissions -> Workflows: Read and write`  
  Only if Envly should edit workflow files in `.github/workflows`.

### Safe installation setup
- Start with `Only selected repositories` installation scope.
- Do not grant `Administration` unless a concrete feature explicitly requires it.
- Follow least privilege and expand permissions only when functionality demands it.

### Suggested v1 preset
`Metadata (RO) + Actions (RW) + Contents (RO) + Deployments (RW)`
