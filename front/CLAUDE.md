# CLAUDE.md — Boukii Admin Panel **V5** (nuevo, desde cero)

> **Propósito**  
> Instrucciones operativas y estándares para trabajar con **Boukii V5** usando asistentes (Claude/Codex) en el **nuevo repo de admin**.  
> La **versión anterior** se mantiene **solo como referencia funcional** (flujos y dominios de negocio). Aquí cambiamos arquitectura, estilos, testing y CI/CD.

---

## 0) Repositorios y ramas
- **Frontend (este repo)**: *Boukii Admin V5* · Angular (objetivo 18.x; válido 16–18 si el workspace aún no migró).
  - URL dev: `http://localhost:4200`
  - Storybook: `http://localhost:6040`
- **Backend**: `api-boukii` (rama `v5`) · Laravel 10+
  - API local: `http://api-boukii.test`
- **Documentación compartida**: `docs/shared/` → sincronizada al backend **solo** esa carpeta mediante PR automático.

> ❗️Evita rutas absolutas de equipos locales en docs (p. ej., `C:\...`). Usa rutas relativas o variables de entorno/`runtime-config.json`.

---

## 1) Entornos y configuración **runtime**
Cargamos configuración **en tiempo de arranque** (sin recompilar para cambiar endpoints).
- Archivos:
  - `assets/config/runtime-config.json`
  - `assets/config/runtime-config.example.json`
- Cargador: `AppConfigService` + `APP_INITIALIZER`
- **Ejemplo**:
```json
{
  "API_BASE_URL": "http://api-boukii.test",
  "FEATURE_FLAGS": {
    "useMocks": false,
    "showDevTools": true
  }
}
```
> No mezclar endpoints en `environment.ts`. El **origen de verdad** para endpoints es `runtime-config.json`.

---

## 2) Comandos de desarrollo (Frontend)
```bash
# Desarrollo
npm start                      # Dev server (http://localhost:4200)
npm run storybook             # Storybook (http://localhost:6040)

# Calidad
npm run lint                  # ESLint
npm run typecheck             # tsc --noEmit
npm run test:ci               # Jest unit tests
npm run build                 # Compila app con budgets
npm run build:storybook       # Compila Storybook
npm run verify                # lint + typecheck + test:ci + build (+ build:storybook si está integrado)
```

**Regla de oro:** ningún cambio entra si `npm run verify` **no está verde**.

---

## 3) Arquitectura Front (standalone + DDD-lite)
```
src/
 ├─ app/
 │   ├─ core/                # servicios base (auth, api http, context), guards, interceptores
 │   ├─ shared/              # utils/pipes/directivas compartidas
 │   ├─ ui/                  # átomos/moléculas (Button, TextField, Breadcrumbs, PageHeader, ...)
 │   ├─ features/
 │   │   ├─ auth/            # login/register/forgot
 │   │   ├─ schools/         # select-school
 │   │   └─ dashboard/       # dashboard skeleton
 │   └─ layout/              # AppShell (navbar+sidebar) + LayoutService
 ├─ assets/
 │   ├─ config/              # runtime-config.json
 │   └─ i18n/                # es.json, en.json, de.json, fr.json, it.json
 └─ styles/
     ├─ tokens.css           # design tokens (CSS variables)
     ├─ light.css            # tema claro
     └─ dark.css             # tema oscuro
```
**Principios**: Standalone Components, Signals cuando aplique, `OnPush`, **sin** colores hardcoded (solo tokens CSS).

---

## 4) Theming (blanco/negro minimalista)
- Tokens en `styles/tokens.css`; temas en `styles/light.css` y `styles/dark.css`.
- `document.body[data-theme="light"|"dark"]` decide tema.
- Storybook con **toolbar** para alternar light/dark y verificar contraste.

---

## 5) Internacionalización (i18n)
- Idiomas: **es** (por defecto), **en**, **de**, **fr**, **it**.
- `TranslationService` + pipe (`t`) con persistencia en `localStorage` y fallback a **es**.
- `LanguageSelectorComponent` accesible (banderas y nombres nativos).
- Nada de textos hardcoded en Auth/UI base: **usar llaves i18n**.

---

## 6) API V5: rutas y patrón de integración
**Rutas típicas (ajusta a tu backend):**
- `POST /api/v5/auth/login`
- `POST /api/v5/auth/register`
- `POST /api/v5/auth/forgot-password`
- `GET  /api/v5/auth/me`
- `GET  /api/v5/schools` (mías)
- `GET  /api/v5/seasons?school_id=:id`
- `GET  /api/v5/dashboard/stats`

**HTTP base**
- `ApiHttpService`: compone URLs con `API_BASE_URL`, maneja **RFC7807**, añade `retry/backoff` en GET idempotentes.

**Autenticación y contexto**
- `AuthV5Service`: `login/register/forgot/me/logout`, Signals (`currentUserSig`), `isAuthenticated()`.
- `auth.interceptor`: `Authorization: Bearer <token>` en rutas privadas.
- `context.interceptor`: añade `X-School-ID` y `X-Season-ID` si existen.
- **Flujo**: Login → (si varias) Select School → (si varias) Select Season → Dashboard.
- Persistencia de `schoolId/seasonId` en `localStorage` (TTL razonable).

**OpenAPI (opcional)**
- `docs/shared/boukii-v5.yaml` como fuente y script `openapi:gen` para generar tipos/cliente en `src/app/data/clients`.

---

## 7) Routing (resumen)
```ts
export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'dashboard' },

  { path: 'auth', children: [
      { path: 'login',    loadComponent: () => import('./features/auth/login.page').then(m => m.LoginPageComponent) },
      { path: 'register', loadComponent: () => import('./features/auth/register.page').then(m => m.RegisterPageComponent) },
      { path: 'forgot',   loadComponent: () => import('./features/auth/forgot-password.page').then(m => m.ForgotPasswordPageComponent) },
  ]},

  { path: '', canActivate: [authGuard], component: AppShellComponent, children: [
      { path: 'select-school', loadComponent: () => import('./features/schools/select-school.page').then(m => m.SelectSchoolPageComponent) },
      { path: 'dashboard', canActivate: [schoolSelectedGuard], loadComponent: () => import('./features/dashboard/dashboard.page').then(m => m.DashboardPageComponent) },
      // futuras rutas privadas…
  ]},

  { path: '**', redirectTo: 'dashboard' }
];
```

---

## 8) Storybook (8.x) — sin conflictos
- Builder alineado (Vite/Webpack) según Angular del workspace.
- Addons: `@storybook/addon-essentials`, `@storybook/addon-a11y`.
- **Toolbar** para tema light/dark (decorator que ajusta `data-theme`).
- Stories **aisladas** (componentes standalone + mocks).
- Si hay legacy: excluir `src/legacy`/`src/v5` del build o usar **alias de stubs**.

Comandos:
```bash
npm run storybook
npm run build:storybook
```

---

## 9) Testing (unit, integración y E2E)
**Unit (Jest)**
- **Cobertura mínima global ≥ 80%** (falla por debajo).
- Suites:
  - UI (atoms/moléculas): render y bindings.
  - Interceptores: 200/401/422/5xx y RFC7807.
  - Guards: auth/school/season.
  - Servicios: AuthV5Service/ApiHttpService.

**Integración (MSW en Storybook)**
- `msw` + `msw-storybook-addon` para mocks de `/auth/login`, `/me`, `/schools`, `/seasons`.
- Escenarios 0/1/N escuelas/temporadas y errores 401/500.

**E2E (Playwright/Cypress)**
- Flujo: login → selectSchool → selectSeason → dashboard.
- Casos: credenciales inválidas; usuario con 1 escuela/temporada (salta selección).
- E2E corre en CI como **required check**.

---

## 10) Lint, formateo y convenciones
- **ESLint** (estricto), **Prettier**, **Husky** (pre-commit), **lint-staged**, **commitlint** (Conventional Commits).
- No relajar reglas globales: si se requiere excepción, **acotar a línea/bloque** y justificar en PR.
- Commits: `feat|fix|refactor|chore|docs|test: ...` (+ `!` si hay breaking).

---

## 11) CI/CD (GitHub Actions)
**Checks en PR a `v5/main`:**
- `ci.yml`: `lint` → `typecheck` → `test:ci` → `build` → `build:storybook` (sube artefacto).
- `e2e.yml`: E2E en PR.
- `quality-gates.yml`: `npm audit` (falla en high/critical) + Sonar (opcional).

**Branch protection**: requerir `ci`, `e2e` (si aplica) y `quality-gates`.  
**CODEOWNERS** recomendado para revisar rutas sensibles.

**Releases** (opcional): `semantic-release` para CHANGELOG y versionado.

---

## 12) Documentación y **sync seguro**
- `docs/frontend/`: Arquitectura, Theming, Routing, Testing, Storybook, Runtime Config, PAGES flow.
- `docs/shared/ENGINEERING_FLOW.md`: Diagrama de flujo (Mermaid) del pipeline.

**Sync de docs al backend**: workflow que **solo** sincroniza `docs/**` abriendo PR en `api-boukii` (anti-bucle activado).  
🔒 **Prohibido** sincronizar fuera de `docs/**`.

---

## 13) Buenas prácticas transversales
- **Accesibilidad**: focus visible, `aria-*`, contraste, navegación con teclado.
- **Seguridad front**: no loguear tokens; sanitizar errores; CSP recomendada a nivel de deploy.
- **Performance**: lazy-loading, budgets, evitar dependencias pesadas sin necesidad.
- **Observabilidad**: logs estructurados (nivel `debug` desactivado en prod), contadores simples de error.
- **RBAC**: sidebar/menu condicionado por permisos; `canMatch` en rutas sensibles.

---

## 14) Guardrails para IA (Claude/Codex)

**Permitido**
- Modificar `src/app/**`, `assets/i18n/**`, `assets/config/**`, `docs/**`, `.github/workflows/**`.
- Crear tests, stories, scripts de CI, MSW handlers.

**Prohibido**
- Tocar `.env` reales o secrets.
- Cambios fuera de `docs/**` en repos cruzados.
- Introducir UI kits o libs pesadas sin consenso.
- Dejar el repo sin compilar o tests en rojo.

**Workflow**
1) Crear **rama** por tarea (alcance pequeño).
2) Ejecutar `npm run verify`.
3) Abrir PR con checklist y evidencias (capturas/artefactos).
4) Merge por **squash** (Conventional Commit).

---

## 15) Backend (Laravel) — recordatorio rápido
- **No** usar `php artisan serve` si Laragon ya expone `http://api-boukii.test`.
- Comandos:
```bash
php artisan migrate
php artisan test
php artisan route:list --path=v5
vendor/bin/pint
vendor/bin/phpstan analyse
```
- **Contrato API V5**: formato de respuesta/documentado en `docs/shared/api/*` y/o OpenAPI.
- **Errores**: RFC 7807 (Problem Details).

---

## 16) Checklist “Ready for merge”
- [ ] Build app y Storybook sin warnings/errores.
- [ ] ESLint a **0** errores.
- [ ] Cobertura **≥ 80%** (Jest).
- [ ] E2E flujo auth/context verde en CI.
- [ ] i18n activo (es/en/de/fr/it) en Auth + UI base.
- [ ] Interceptores / guards OK (headers contexto).
- [ ] `runtime-config.json` presente/ejemplo.
- [ ] Docs actualizadas y **sync** restringido a `docs/**`.

---

**Última actualización:** 2025-08-18  
**Cómo pedir ayuda a la IA:** crea issue con etiqueta `needs-claude` y describe: objetivo, alcance, criterios de aceptación y comandos de verificación.
