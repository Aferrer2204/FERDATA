# Detalles de despliegue

Este documento resume la configuración de servidores, bases de datos y ambientes utilizados para deploy y pruebas.

Entornos
- Producción:
  - Frontend: Servido desde Nginx/Apache en servidor `app.ferdata.com` (TLS habilitado)
  - Backend API (Node): `https://api.ferdata.com` (puerto 443, cluster PM2)
  - Base de datos: MySQL 8.0 en host `db.ferdata.com`, usuario con permisos limitados
  - Backup: `mysqldump` diario a `/backups` y copia a S3
- Staging:
  - Frontend/Backend desplegado en `staging.ferdata.com`
  - DB: instancia separada con datos de prueba

Configuración de servidor (ejemplo Apache + PHP + Node):
- Apache DocumentRoot: `/var/www/ferdata/frontend`
- PHP scripts: `/var/www/ferdata/api/`
- Node backend: `/opt/ferdata/backend` ejecutado con PM2: `pm2 start server.js --name ferdata-backend`

Base de datos:
- Nombre: `magnatesting_db`
- Usuario de aplicación: `ferdata_app` (acceso limitado)
- Scripts de migración: `database/migrations/` (ejecutar en orden numérico)

Variables de entorno (ejemplo `.env`):
- NODE_ENV=production
- DB_HOST=127.0.0.1
- DB_USER=ferdata_app
- DB_PASS=********
- DB_NAME=magnatesting_db
- JWT_SECRET=********

Procedimiento de restauración rápida (producción -> local):
1. Copiar `database/magnatesting_db.sql`
2. `mysql -u root -p magnatesting_db < magnatesting_db.sql`
3. Editar `backend/.env` con credenciales locales
4. `cd backend && npm install && node server.js`
