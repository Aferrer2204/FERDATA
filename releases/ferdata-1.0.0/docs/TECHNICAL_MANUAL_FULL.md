# Manual Técnico Completo

Este documento complementa `TECHNICAL_MANUAL.md` con detalles de construcción, dependencias y comprobaciones rápidas.

## Requisitos
- Node.js >= 18
- MySQL >= 5.7 (preferible 8.0)
- PHP >= 7.4 (para endpoints legacy)

## Dependencias principales
- backend/package.json lista `express`, `mysql2`, `jsonwebtoken`, `cors`.

## Build y empaquetado
- `scripts/create_release.ps1` empaqueta release para windows.

## Pruebas
- Ejecutar scripts en `backend/scripts/` para validar conteos y endpoints.

## Entregables y estructura esperada
- `releases/ferdata-<version>.zip` contiene el paquete ejecutable y README de despliegue.
