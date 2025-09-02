---
# Manual Técnico (UNIFICADO)

Este documento combina el `TECHNICAL_MANUAL.md` resumido y el `TECHNICAL_MANUAL_FULL.md` completo en una sola referencia práctica.

## 1. Arquitectura
- Frontend: HTML/JS estático en `frontend/` (consume APIs PHP/Node)
- Backend legacy: PHP en `api/` (pensado para despliegues en Apache + PHP)
- Backend moderno: Node.js en `backend/` (Express, rutas y controladores)
- Base de datos: MySQL (esquema y migraciones en `database/`)

## 2. Requisitos
- Node.js >= 18
- MySQL >= 5.7 (recomendado 8.0)
- PHP >= 7.4 (para endpoints legacy)

## 3. Dependencias principales
- Node (backend/package.json): `express`, `mysql2`, `jsonwebtoken`, `cors`.

## 4. Despliegue (pasos generales)
1. Restaurar base de datos (ejemplo):

```powershell
mysql -u root -p magnatesting_db < database/schema.sql
```

2. Servir frontend: copiar `frontend/` al DocumentRoot de Apache o servir estático desde Node (opcional).
3. Asegurar `api/` (PHP) accesible desde Apache (permisos y rutas).
4. Opcional: ejecutar Node backend (si se desea usar la API Node):

```powershell
cd backend
npm install
node server.js
```

## 5. Build y empaquetado
- Windows: `scripts/create_release.ps1` empaqueta la release.
- Releases esperadas: `releases/ferdata-<version>.zip` contiene paquete y README.

## 6. Backups
- Programar `mysqldump` diario y guardar en `backups/` con fecha. Si `mysqldump` no está disponible, se provee un script alternativo que exporta datos a JSON y genera SQL (`scripts/export_db_no_mysqldump.js` y `scripts/generate_sql_inserts_from_json.js`).

## 7. Variables de entorno
- Revisar `.env.example` y crear `.env` con valores reales (DB creds, JWT secret, etc.). Evitar subir `.env` a git.

## 8. Pruebas
- Tests rápidos: `scripts/smoke_tests.js` (compara endpoints con archivos en `deliverables/`).
- Scripts útiles en `backend/scripts/` para inspección y conteos (`check_counts.js`, `describe_*` scripts).

## 9. Entregables y estructura esperada
- `releases/ferdata-<version>.zip` — paquete de despliegue con `backend/`, `frontend/`, y `README`.
- `releases/ferdata-<version>/db_exports/` — exports JSON + schema cuando `mysqldump` no está disponible.

## 10. Comprobaciones rápidas (smoke)
- Levantar Node (si se usa): `node backend/server.js`.
- Ejecutar: `node scripts/smoke_tests.js` desde la raíz para verificar endpoints básicos.

## 11. Notas de mantenimiento
- Mantener `deliverables/` y `releases/` fuera de `.gitignore` si quieres que se versionen en el repo.
- Limpiar la carpeta `tmp/` periódicamente.


