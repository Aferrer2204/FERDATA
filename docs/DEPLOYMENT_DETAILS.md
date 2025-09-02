# Deployment details — FERDATA

Este documento recoge instrucciones prácticas para desplegar y probar FERDATA en entornos locales y en servidores (Node/Express y Apache+PHP). Incluye: Quickstart local, Docker Compose, migraciones, restauración de DB y notas de producción.

---

## Resumen rápido
- Backend Node (desarrollador): `http://localhost:3000/` (API en `/api`)
- Frontend (estático o servido desde Node): `http://localhost:3000/` o vía Apache/XAMPP en `http://localhost/FERDATA/frontend/`
- PHP fallback (XAMPP/Apache): scripts en `api/` accesibles desde `http://localhost/FERDATA/api/`
- Base de datos por defecto en repositorio: MySQL `magnatesting_db`

---

## Prerrequisitos
- Node.js 18+ (o la versión usada en `backend/package.json`)
- npm o yarn
- MySQL 8.0 (local o en contenedor)
- Opcional: XAMPP (Apache + PHP) si quiere usar los endpoints PHP legacy
- Opcional: Docker & docker-compose para levantar una pila reproducible

---

## Variables de entorno (.env)
Coloque un archivo `.env` en `backend/` con estas variables mínimas:

```
NODE_ENV=development
PORT=3000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=ferdata_app
DB_PASS=changeme
DB_NAME=magnatesting_db
JWT_SECRET=change_this_to_a_secure_secret
APACHE_API=http://localhost/FERDATA/api
```

Nota: en entornos de producción sustituya las variables por valores seguros y gestione secretos fuera del repo.

---

## Quickstart local (Node)
1) Instalar dependencias del backend:

```powershell
cd C:\xampp\xampp\FERDATA\backend
npm install
```

2) Asegúrese de que MySQL está corriendo y la DB `magnatesting_db` existe (vea sección "Restauración / crear DB" abajo).

3) Arrancar el servidor Node (desarrollo):

```powershell
$env:PORT=3000; node server.js
```

4) Abrir en el navegador:

- Panel principal: http://localhost:3000/
- Dashboard (Control de Mando): http://localhost:3000/dashboard

---

## Quickstart local (XAMPP / Apache + PHP fallback)
1) Copie todo el repositorio a la carpeta que Apache sirve (ej. `C:\xampp\htdocs\FERDATA` o según su configuración de DocumentRoot).
2) Asegúrese de que XAMPP/Apache y MySQL están activos.
3) Acceda a:

- Frontend: http://localhost/FERDATA/frontend/MODULO_CONTROL_DE_MANDO.html
- PHP API: http://localhost/FERDATA/api/empresas/read.php

El frontend del proyecto ya contiene lógica para intentar primero los endpoints Node (`/api/*`) y, en caso de respuesta 404 o `Cannot GET`, usar los scripts PHP como fallback.

---

## Docker Compose (pila reproducible)
El repositorio incluye un `docker-compose.yml` de ejemplo con servicios: `db` (MySQL), `php-apache` (sirve frontend y `api/`) y `phpmyadmin`.

Levante la pila:

```powershell
cd C:\xampp\xampp\FERDATA
docker-compose up -d
```

Comprobaciones:

- MySQL: `localhost:3306` (usuario root / contraseña definida en `docker-compose.yml`)
- php-apache: http://localhost:8080/ (frontend)
- phpmyadmin: http://localhost:8081/

Nota: el servicio Node no está incluido en el `docker-compose.yml` por defecto; puede ejecutar Node en su host apuntando a la DB del contenedor (`DB_HOST=127.0.0.1` o la IP de la red de Docker según su configuración) o extender `docker-compose` con un servicio `node-backend`.

---

## Migraciones y creación de esquema
- Las migraciones (si existen) están en `database/migrations/`. Ejecute los scripts SQL en orden numérico contra la base de datos.
- Si dispone de un archivo SQL (p.ej. `database/magnatesting_db.sql`) puede restaurarlo:

```powershell
mysql -u root -p magnatesting_db < ./database/magnatesting_db.sql
```

Si no dispone de `mysqldump` en su entorno, use los scripts JSON→SQL incluidos (`scripts/export_db_no_mysqldump.js` y `scripts/generate_sql_inserts_from_json.js`) para generar un `.sql` válido.

---

## Restauración rápida (producción -> local)
1) Obtener el SQL exportado del entorno de producción (si lo tiene) o usar `deliverables/` JSON.
2) Crear la base de datos local y restaurar:

```powershell
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS magnatesting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p magnatesting_db < magnatesting_db.sql
```

3) Actualizar `backend/.env` con credenciales locales y arrancar Node (ver Quickstart).

---

## Despliegue en producción (resumen)
- Use un servicio process manager para Node: `pm2 start server.js --name ferdata-backend` (asegure PM2 y la variable `NODE_ENV=production`).
- Configure un proxy inverso (Nginx/Apache) para exponer el backend en `https://api.ferdata.com` y el frontend en `https://app.ferdata.com`.
- Habilite TLS (Let’s Encrypt) y copias de seguridad periódicas de la base de datos (`mysqldump` o snapshots gestionados).

Ejemplo (PM2):

```powershell
# en servidor Linux (ejemplo)
pm2 start /opt/ferdata/backend/server.js --name ferdata-backend --env production
pm2 save
```

---

## Comprobaciones y salud
- Endpoint de health del backend: `GET /health` → devuelve `{ status: 'ok' }` cuando Node corre.
- Logs del backend se escriben en `backend/logs/server.log`.

---

## Pruebas automatizadas y smoke tests
- Ejecutar tests rápidos registrados en `scripts/smoke_tests.js` compara las respuestas live con `deliverables/*.json`:

```powershell
cd C:\xampp\xampp\FERDATA
node scripts/smoke_tests.js
```

Los resultados se graban en `tmp/smoke_results.json`.

---

## Troubleshooting (problemas comunes)
- "Cannot GET /api/..." desde el frontend: el servidor Node no está corriendo o la ruta no está implementada; abra `http://localhost:3000/api/<ruta>` para probar.
- Respuestas HTML inesperadas en endpoints API: asegúrese de que las llamadas se dirigen a Node (`http://localhost:3000/api`) o al PHP correcto; el servidor Node incluye un handler de errores que debe devolver siempre JSON para rutas `/api`.
- Conexiones a MySQL: verifique `DB_HOST`, `DB_USER`, `DB_PASS` en `backend/.env`.
