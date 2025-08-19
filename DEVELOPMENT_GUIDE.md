# Boukii Admin V5 - Development Guide

## 🛠 Development Workflow

Este proyecto utiliza herramientas automatizadas de calidad de código para mantener estándares consistentes.

### 📋 Scripts Disponibles

#### Development

```bash
npm start                    # Servidor de desarrollo
npm run build               # Build de desarrollo
npm run build:production    # Build de producción
npm run preview             # Preview del build de producción
```

#### Quality Assurance

```bash
npm run lint                # Verificar reglas de ESLint
npm run lint:fix            # Corregir automáticamente errores de ESLint
npm run format              # Formatear código con Prettier
npm run format:check        # Verificar formato sin cambios
npm run typecheck           # Verificar tipos de TypeScript
npm run code-quality        # Ejecutar todas las verificaciones
npm run code-quality:fix    # Ejecutar todas las correcciones automáticas
```

#### Testing

```bash
npm test                    # Ejecutar tests con Jest
npm run test:watch          # Tests en modo watch
npm run test:ci             # Tests para CI (con coverage)
npm run storybook           # Ejecutar Storybook
npm run build:storybook     # Build de Storybook
```

#### Git Workflow

```bash
npm run commit              # Commit interactivo con conventional commits
npm run commit:retry        # Reintentar el último commit
npm run hooks:install       # Instalar hooks de Git
npm run hooks:uninstall     # Desinstalar hooks de Git
```

### 🎯 Git Hooks Automatizados

#### Pre-commit Hook

Ejecuta automáticamente antes de cada commit:

- ✅ **ESLint** con corrección automática en archivos modificados
- ✅ **Prettier** formateo en archivos modificados
- ✅ **Type checking** en archivos TypeScript

#### Commit-msg Hook

Valida el mensaje de commit:

- ✅ **Conventional Commits** format required
- ✅ Longitud y formato del mensaje
- ✅ Tipos válidos: feat, fix, docs, style, refactor, test, chore, etc.

#### Pre-push Hook

Ejecuta antes de hacer push:

- ✅ **TypeScript compilation** check completo
- ✅ Verificación de tipos en todo el proyecto

### 📝 Conventional Commits

Este proyecto sigue la especificación [Conventional Commits](https://www.conventionalcommits.org/).

#### Formato

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

#### Tipos Válidos

- **feat**: Nueva funcionalidad
- **fix**: Corrección de bugs
- **docs**: Cambios en documentación
- **style**: Cambios de formato (espacios, punto y coma, etc.)
- **refactor**: Refactoring de código
- **test**: Agregado o corrección de tests
- **chore**: Tareas de mantenimiento
- **perf**: Mejoras de rendimiento
- **ci**: Cambios en CI/CD
- **build**: Cambios en build system

#### Ejemplos

```bash
feat(auth): add OAuth2 authentication
fix(api): resolve timeout issue in user service
docs(readme): update installation instructions
style(forms): improve button spacing
refactor(utils): extract common validation logic
test(auth): add unit tests for login component
chore(deps): update Angular to v18.2
```

#### Commit Interactivo

Usa el script para commits guiados:

```bash
npm run commit
```

### 🔧 ESLint Configuration

El proyecto usa una configuración ESLint avanzada con:

#### TypeScript Rules

- ✅ **No explicit any** - Evitar uso de `any`
- ✅ **Explicit member accessibility** - Modificadores públicos/privados requeridos
- ✅ **Function return types** - Tipos de retorno explícitos
- ✅ **Prefer nullish coalescing** - Usar `??` en lugar de `||`
- ✅ **Prefer optional chaining** - Usar `?.` para acceso seguro

#### Angular Rules

- ✅ **Component selectors** - Prefijo `app-` y kebab-case
- ✅ **Directive selectors** - Prefijo `app` y camelCase
- ✅ **OnPush change detection** - Recomendado para performance
- ✅ **Lifecycle interfaces** - Implementar interfaces de ciclo de vida
- ✅ **Template accessibility** - Reglas de accesibilidad en templates

#### Code Quality Rules

- ✅ **Complexity limit** - Máximo 10 de complejidad ciclomática
- ✅ **Function length** - Máximo 50 líneas por función
- ✅ **File length** - Máximo 500 líneas por archivo
- ✅ **Parameter limit** - Máximo 4 parámetros por función

### 🎨 Prettier Configuration

Prettier está configurado para trabajar sin conflictos con ESLint:

#### Configuración Principal

```json
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 100,
  "tabWidth": 2,
  "useTabs": false
}
```

#### Configuraciones Específicas

- **HTML**: 120 caracteres, espaciado optimizado para Angular
- **SCSS/CSS**: 120 caracteres, comillas dobles
- **JSON**: Sin trailing commas
- **Markdown**: 80 caracteres, wrap automático

### 🚀 Best Practices

#### 1. Antes de Commitear

```bash
# Verificar que todo esté bien
npm run code-quality

# Si hay errores automáticamente corregibles
npm run code-quality:fix

# Hacer commit interactivo
npm run commit
```

#### 2. Durante el Desarrollo

```bash
# Mantener el código formateado
npm run format

# Verificar tipos periódicamente
npm run typecheck

# Ejecutar tests durante desarrollo
npm run test:watch
```

#### 3. Antes de Push

```bash
# El pre-push hook ejecutará automáticamente:
# - Type checking completo
# - Verificación de que el código compila
```

#### 4. Configuración del Editor

##### VS Code (Recomendado)

El proyecto incluye configuración de VS Code que habilita:

- ✅ Formateo automático al guardar
- ✅ ESLint fix automático
- ✅ Organización de imports automática
- ✅ TypeScript intellisense optimizado

##### Extensiones Recomendadas

- ESLint (`dbaeumer.vscode-eslint`)
- Prettier (`esbenp.prettier-vscode`)
- Angular Language Service (`angular.ng-template`)
- TypeScript Hero (`rbbit.typescript-hero`)

### 🐛 Troubleshooting

#### ESLint Errors

```bash
# Ver todos los errores
npm run lint

# Corregir automáticamente
npm run lint:fix

# Para errores no corregibles automáticamente:
# - Revisar mensajes específicos
# - Refactorizar código según las reglas
# - Usar // eslint-disable-next-line solo si es necesario
```

#### Prettier Conflicts

```bash
# Verificar formato
npm run format:check

# Aplicar formato
npm run format:write

# Si persisten conflictos:
# - Verificar .prettierrc.json
# - Comprobar que ESLint no tenga reglas de formato activas
```

#### Git Hooks Issues

```bash
# Reinstalar hooks
npm run hooks:uninstall
npm run hooks:install

# Verificar permisos (Linux/Mac)
chmod +x .husky/pre-commit
chmod +x .husky/commit-msg
chmod +x .husky/pre-push
```

#### TypeScript Errors

```bash
# Verificar tipos sin compilar
npm run typecheck

# Para errores complejos:
# - Verificar tsconfig.json
# - Comprobar imports y exports
# - Usar TypeScript strict mode
```

### 📊 Métricas de Calidad

El proyecto mantiene los siguientes estándares:

- ✅ **0 ESLint errors** en producción
- ✅ **0 TypeScript errors** en producción
- ✅ **100% Prettier compliance**
- ✅ **Conventional commits** en todos los commits
- ✅ **Test coverage** > 80% (objetivo)
- ✅ **Build success** en todos los environments

### 🔄 CI/CD Integration

Los mismos checks que se ejecutan localmente se ejecutan en CI:

1. **Pre-commit checks** locales
2. **CI Pipeline** en GitHub Actions:
   - Lint & Format verification
   - TypeScript compilation
   - Test execution with coverage
   - Build verification
   - Storybook build

Esta configuración garantiza que el código que llega a main siempre cumple con los estándares de calidad.
