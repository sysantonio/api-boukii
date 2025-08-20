# 🚀 Boukii Admin V5

> **Enterprise Angular 18 Admin Panel** con arquitectura DDD, signals state management, y CI/CD completo

[![CI/CD Pipeline](https://github.com/your-org/boukii-admin-v5/workflows/CI%2FCD%20Pipeline/badge.svg)](https://github.com/your-org/boukii-admin-v5/actions)
[![Security Audit](https://github.com/your-org/boukii-admin-v5/workflows/Security%20Audit/badge.svg)](https://github.com/your-org/boukii-admin-v5/actions)
[![Quality Gate](https://img.shields.io/badge/Quality%20Gate-A-brightgreen)](https://github.com/your-org/boukii-admin-v5)
[![Bundle Size](https://img.shields.io/badge/Bundle%20Size-<2MB-green)](https://github.com/your-org/boukii-admin-v5)
[![Coverage](https://img.shields.io/badge/Coverage-90%25-brightgreen)](https://github.com/your-org/boukii-admin-v5)
[![Storybook](https://img.shields.io/badge/Storybook-Docs-ff4785?logo=storybook&logoColor=white)](https://your-org.github.io/boukii-admin-v5/)

**Boukii Admin V5** es un panel de administración empresarial moderno construido con **Angular 18**, implementando las mejores prácticas de desarrollo, arquitectura escalable y automatización completa de CI/CD.

## ✨ Características Principales

### 🏗️ **Arquitectura Enterprise**

- **Domain-Driven Design (DDD)** con capas bien definidas
- **Standalone Components** de Angular 18
- **Signals-based State Management** reactivo
- **Dependency Injection** avanzado
- **Modular Architecture** con lazy loading

### 🎨 **UI/UX Moderna**

- **Design System** completo con tokens
- **Dark/Light Theme** con CSS custom properties
- **Responsive Design** mobile-first
- **Accessibility (a11y)** compliance
- **Component Library** con Storybook

### 🔄 **State Management**

- **Angular Signals** para reactividad
- **Stores** inmutables con TypeScript
- **Side Effects** management
- **Optimistic Updates** pattern
- **Error Boundaries** global

### 🌐 **Internacionalización**

- **i18n** en/es con lazy loading
- **Runtime Language Switching**
- **Type-safe Translations**
- **Pluralization Rules**
- **Date/Number Formatting**

### 🛠️ **Developer Experience**

- **Hot Module Replacement** (HMR)
- **TypeScript Strict Mode**
- **ESLint + Prettier** automation
- **Git Hooks** con Husky
- **Conventional Commits** enforcement

### 🔒 **Security & Quality**

- **Security Headers** configurados
- **Dependency Vulnerability** scanning
- **Code Quality Gates** automatizados
- **Performance Budget** enforcement
- **OWASP** compliance

### 🚀 **CI/CD Enterprise**

- **GitHub Actions** pipelines
- **Multi-environment** deployment
- **Quality Gates** automáticos
- **Performance Monitoring**
- **Rollback** automático

## 🚦 Quick Start

### Prerrequisitos

- **Node.js** >= 20.0.0
- **npm** >= 10.0.0
- **Git** >= 2.40.0

### Instalación

```bash
# Clonar repositorio
git clone https://github.com/your-org/boukii-admin-v5.git
cd boukii-admin-v5

# Instalar dependencias
npm install

# Configurar entorno de desarrollo
npm run config:development

# Iniciar servidor de desarrollo
npm start
```

La aplicación estará disponible en `http://localhost:4200`

### Scripts Principales

```bash
# Desarrollo
npm start                    # Servidor de desarrollo con HMR
npm run build               # Build de desarrollo
npm run preview             # Preview del build

# Testing
npm test                    # Tests unitarios con Jest
npm run test:watch          # Tests en modo watch
npm run test:ci             # Tests con coverage para CI

# Calidad de Código
npm run lint                # Verificar ESLint
npm run lint:fix            # Corregir errores automáticamente
npm run format              # Formatear con Prettier
npm run typecheck           # Verificar tipos TypeScript

# Análisis
npm run analyze:code        # Análisis de calidad completo
npm run analyze:bundle      # Análisis de bundle size
npm run quality:report      # Reporte de calidad completo

# Storybook
npm run storybook           # Servidor de Storybook
npm run build:storybook     # Build de Storybook

# Git Workflow
npm run commit              # Commit interactivo
npm run hooks:install       # Instalar Git hooks
```

## 📁 Estructura del Proyecto

```
src/
├── app/
│   ├── core/                    # Core layer (servicios fundamentales)
│   │   ├── config/             # Configuración de aplicación
│   │   ├── guards/             # Route guards
│   │   ├── interceptors/       # HTTP interceptors
│   │   ├── models/             # Modelos de dominio
│   │   ├── services/           # Servicios core
│   │   └── stores/             # State management
│   │
│   ├── shared/                  # Shared layer (componentes reutilizables)
│   │   ├── components/         # Componentes compartidos
│   │   ├── directives/         # Directivas compartidas
│   │   └── pipes/              # Pipes compartidos
│   │
│   ├── features/                # Feature layer (funcionalidades)
│   │   ├── dashboard/          # Dashboard principal
│   │   ├── auth/               # Autenticación
│   │   └── ...                 # Otras features
│   │
│   ├── ui/                      # UI layer (layout y componentes base)
│   │   ├── app-shell/          # Layout principal
│   │   ├── theme-toggle/       # Control de tema
│   │   └── ...                 # Otros componentes UI
│   │
│   └── state/                   # State layer (gestión de estado global)
│       ├── auth/               # Estado de autenticación
│       ├── ui/                 # Estado de UI
│       └── ...                 # Otros estados
│
├── assets/                      # Assets estáticos
│   ├── config/                 # Configuración runtime
│   ├── i18n/                   # Archivos de traducción
│   └── icons/                  # Iconografía
│
├── environments/                # Configuración de entornos
├── styles/                      # Estilos globales y tokens
└── ...
```

## 🛠️ Development Workflow

### Conventional Commits

```bash
# Formato requerido
<type>[optional scope]: <description>

# Ejemplos
feat(auth): add OAuth2 authentication
fix(api): resolve timeout issue in user service
docs(readme): update installation instructions
refactor(utils): extract common validation logic
test(auth): add unit tests for login component
chore(deps): update Angular to v18.2
```

### Quality Assurance

El proyecto implementa quality gates estrictos:

- ✅ **ESLint**: 0 errores permitidos
- ✅ **Prettier**: 100% compliance
- ✅ **TypeScript**: Strict mode
- ✅ **Test Coverage**: >80%
- ✅ **Bundle Size**: <2MB
- ✅ **Performance**: Lighthouse >90

```bash
npm run code-quality            # Verificar toda la calidad
npm run code-quality:fix        # Corregir automáticamente
npm run analyze:code            # Análisis completo de calidad
```

## 🚀 Deployment

### Ambientes

- **Development**: Auto-deploy desde `develop` branch
- **Staging**: Auto-deploy desde `develop` branch
- **Production**: Auto-deploy desde `main` branch (con approval)

### Proceso de Deploy

1. **CI Pipeline**: Build, test, lint, security scan
2. **Quality Gate**: Score mínimo 70/100
3. **Security Validation**: 0 vulnerabilidades críticas
4. **Performance Check**: Bundle <2MB, Lighthouse >90
5. **Deploy**: Blue-green deployment con health checks
6. **Monitoring**: Verificación post-deploy automática

## 📚 Documentación Adicional

- 📖 **[Development Guide](DEVELOPMENT_GUIDE.md)** - Guía completa de desarrollo
- 🏗️ **[Architecture Guide](docs/ARCHITECTURE.md)** - Documentación de arquitectura
- 🔒 **[Security Guide](docs/SECURITY.md)** - Guía de seguridad
- 🚀 **[Deployment Guide](docs/DEPLOYMENT.md)** - Guía de deployment

## 🤝 Contributing

1. **Fork** el repositorio
2. **Create** feature branch: `git checkout -b feature/mi-feature`
3. **Make** changes siguiendo las guidelines
4. **Test**: `npm run code-quality`
5. **Commit**: `npm run commit`
6. **Push** y crear **Pull Request**

## 📄 License

Este proyecto está licenciado bajo [MIT License](LICENSE).

---

<div align="center">

**🚀 Built with ❤️ by the Boukii Team**

[![Angular](https://img.shields.io/badge/Angular-18-red?logo=angular)](https://angular.io)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.5-blue?logo=typescript)](https://typescriptlang.org)
[![GitHub Actions](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?logo=github-actions)](https://github.com/features/actions)

[⭐ Star this repo](https://github.com/your-org/boukii-admin-v5) • [🐛 Report Bug](https://github.com/your-org/boukii-admin-v5/issues) • [💡 Request Feature](https://github.com/your-org/boukii-admin-v5/issues)

</div>
