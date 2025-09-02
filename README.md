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

Documentación adicional: revisar archivos en `docs/` y actualizar con datos reales (URLs, resultados de pruebas, manuales).
# FERDATA

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

Generar paquete de entrega (release)

Se incluye un script para crear un paquete zip con los artefactos requeridos:

```powershell
.\scripts\create_release.ps1 -Version 1.0.0 -OutputDir .\releases
```

El script copia `frontend/`, `backend/`, `api/`, `database/` y `docs/` dentro de `releases/ferdata-<version>.zip`. Si `mysqldump` está disponible intentará incluir un volcado del esquema.

Documentación y entregables
- Actualice y consulte:
	- `docs/DEPLOYED_URLS.md` — URLs desplegadas por entorno
	- `docs/MODULES.md` — documentación por módulo (I/O, endpoints)
	- `docs/TEST_REPORTS.md` — registro de pruebas y resultados
	- `docs/DEPLOYMENT_DETAILS.md` — configuraciones de servidores y DB
	- `docs/TECHNICAL_MANUAL_FULL.md` — manual técnico extendido
	- `DELIVERABLES.md` — checklist de entrega

Pruebas
- Añadir casos y resultados en `docs/TEST_REPORTS.md`. Los logs y evidencias están en `backend/logs/`.

Control de versiones
- Flujo recomendado en `CONTRIBUTING.md` (Git): ramas feature, PRs, tags y releases.

Soporte y contacto
- Para asistencia con despliegue o creación de release, incluye la información de tu entorno (SO, versiones, puertos) y puedo generar el paquete y los pasos exactos para tu plataforma.

Contribuciones
- Sigue `CONTRIBUTING.md` para normas de commits, ramas y releases.

Licencia
- Indicar la licencia del proyecto aquí (p. ej. MIT) o en `LICENSE`.

## Gestión del repositorio para entrega de código fuente

Este proyecto debe entregarse usando un repositorio de control de versiones. A continuación hay una guía práctica y comandos recomendados para preparar y entregar el código fuente de forma consistente.

1. Modelo de ramas
	- `main` (o `master`): versión estable y lista para release. Sólo se mergean cambios comprobados.
	- `develop` (opcional): integración diaria para features completadas y pruebas.
	- `feature/*`: trabajar en nuevas funcionalidades (branch corta por feature).
	- `hotfix/*`: correcciones urgentes aplicadas directamente sobre `main` y luego mergeadas a `develop`.

2. Buenas prácticas de commits
	- Mensajes claros: tipo(scope): breve descripción. Ej.: `feat(auth): añadir login con JWT`.
	- Hacer commits pequeños y atómicos. Ejecutar pruebas locales antes de commitear.

3. Ignorar archivos que no se entregan
	- Mantener `node_modules/`, ficheros temporales y backups fuera del repo usando `.gitignore`.
	- Ejemplos: `backend/node_modules/`, `*.sqlite`, `*.log`, `*.git.bak`.

4. Releases y tags
	- Usar versionado semántico (semver): `MAJOR.MINOR.PATCH` (ej. 1.0.0).
	- Crear tag firmado y un release: `git tag -a v1.0.0 -m "Release 1.0.0"` y `git push origin v1.0.0`.
	- Empaquetado: usar el script provisto para crear el artefacto de release:

```powershell
.
.\scripts\create_release.ps1 -Version 1.0.0 -OutputDir .\releases
```

5. Archivos adicionales para entrega
	- `LICENSE` (texto de la licencia usada).
	- `CHANGELOG.md` con los cambios por versión.
	- `DELIVERABLES.md` o `docs/` explicando qué se incluye en el release.

6. Submódulos y carpetas anidadas
	- Evitar dejar submódulos sin declarar en `.gitmodules`. Para entregables prefiera carpetas normales dentro del repo o documente y versione correctamente los submódulos.

7. Seguridad y artefactos grandes
	- No incluir secretos (`.env`), claves ni backups (`*.git.bak`) en el repo.
	- Para artefactos grandes (dependencias, binarios) genere paquetes externos (zip) y publíquelos en `releases/`.

8. Checklist mínimo antes de entregar
	- `git status --porcelain -b` — asegurar que el workspace está limpio.
	- `git add README.md .gitignore backend` (o los archivos que quieres incluir).
	- `git commit -m "docs: añadir guía de entrega del repositorio"`.
	- `git tag -a vX.Y.Z -m "Release vX.Y.Z"`.
	- `git push origin HEAD --follow-tags`.

Si quieres, puedo: crear un fichero `LICENSE` con la licencia `ISC` (ya indicada en `package.json`), añadir `CHANGELOG.md` o hacer el commit por ti ahora.

