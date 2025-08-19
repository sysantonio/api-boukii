# Core Module

Servicios globales, interceptores, guards y configuración de la aplicación.

## Estructura

```
core/
├── services/          # Servicios globales (auth, config, theme)
├── interceptors/      # HTTP interceptores
├── guards/           # Route guards
├── models/           # Interfaces y tipos globales
└── config/           # Configuración de la aplicación
```

## Uso

Los servicios en `core` son singletons que se cargan una sola vez en la aplicación.
