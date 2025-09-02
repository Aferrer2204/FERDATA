# Manual Técnico (resumen)

## Arquitectura
- Frontend: HTML/JS estático en `frontend/` (consume APIs PHP/Node)
- Backend PHP: `api/` (compatible con Apache + PHP)
- Backend Node (opcional): `backend/` (Express)
- DB: MySQL (schema en `database/`)

## Despliegue (pasos generales)
1. Restaurar base de datos: `mysql -u root -p magnatesting_db < database/schema.sql`
2. Copiar `frontend/` al DocumentRoot de Apache
3. Asegurar `api/` accesible desde Apache (permiso y rutas)
4. Opcional: ejecutar Node backend `cd backend && npm install && node server.js`

## Backups
- Programar `mysqldump` diario y guardar en `backups/` con fecha.

## Variables de entorno
Revisar `.env.example` y crear `.env` con valores reales.
