# FERDATA - Entrega y documentación

Este repositorio contiene el código del proyecto FERDATA  entregables requeridos.

Principales artefactos añadidos para la entrega:

- `docs/` : plantillas de documentación por módulo, manual técnico y archivo con URLs desplegadas.
- `scripts/create_release.ps1` : empaqueta backend/frontend, copia migraciones y genera un zip de release.


Cómo crear un release (PowerShell):

```powershell
cd C:\xampp\xampp\FERDATA
.\scripts\create_release.ps1 -Version 1.0.0 -OutputDir .\releases
```

Cómo levantar localmente con Docker (ejemplo):

```powershell
docker-compose up -d --build
# frontend accessible at http://localhost:8080
# phpMyAdmin at http://localhost:8081
```

FERDATA es una aplicación web para la gestión de empresas, herramientas e inspecciones. Este repositorio contiene el frontend estático, endpoints PHP legacy y un backend Node.js opcional.

Contenido principal
- `frontend/` - páginas y recursos estáticos (HTML/CSS/JS)
- `api/` - endpoints PHP compatibles con Apache/XAMPP
- `backend/` - servidor Node.js (Express) con controladores y rutas
- `database/` - esquemas, migraciones y scripts SQL
- `docs/` - documentación, manuales y plantillas para entregables
- `scripts/` - utilidades (p. ej. `create_release.ps1`)

Requisitos
- Node.js >= 14 (para el backend opcional)
- MySQL (la base de datos principal, ver `database/`)
- PHP 7.4+ y Apache (si desea usar los endpoints PHP locales)
- PowerShell (Windows) para ejecutar los scripts de empaquetado incluidos

Inicio rápido (desarrollo local)

1) Clonar el repositorio

```powershell
git clone <repo.git>
cd FERDATA
```

2) Configurar la base de datos

- Crear la base de datos y cargar el esquema (ejemplo):

```powershell
# Ajustar usuario/contraseña según su entorno
mysql -u root -p magnatesting_db < database/magnatesting_db.sql
```

3) Levantar servicios

- Opción A — Usar Apache/PHP + MySQL (XAMPP): copiar `frontend/` a DocumentRoot y dejar `api/` accesible.
- Opción B — Usar Docker Compose (ejemplo si tiene Docker instalado):

```powershell
docker-compose up -d --build
# frontend: http://localhost:8080 (según docker-compose)
# phpMyAdmin: http://localhost:8081
```

- Opción C — Ejecutar backend Node (opcional):

```powershell
cd backend
npm install
node server.js
# el backend normalmente escucha en http://localhost:3000
```

Autenticación
- El frontend usa `localStorage.token` y `localStorage.user`. El flujo de login está en `frontend/js/auth.js`.

Endpoints API relevantes (resumen)
- `POST /api/auth/login` — iniciar sesión
- `POST /api/auth/register` — registrar usuario
- `GET /api/empresas` — listar empresas
- `GET /api/herramientas` — listar herramientas
- `GET /api/inspecciones` — listar inspecciones (con `empresa_nombre` y `herramienta_nombre`)
- `GET /api/actividades` — listar actividades
