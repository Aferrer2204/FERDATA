# Documentación por Módulo y Componentes

Este documento contiene, por módulo, la descripción funcional, los endpoints usados, los datos de entrada/salida y pruebas realizadas.

Formato por módulo:
- Nombre del módulo
- Componentes (archivos relevantes)
- Endpoints / rutas
- Entradas (payloads) y salidas (JSON)
- Casos de prueba y resultados

## 1. Autenticación (Auth)
- Archivos: `backend/controllers/authController.js`, `api/auth/*`, `frontend/js/auth.js`
- Endpoints:
	- `POST /api/auth/register` - payload: { nombre_completo, email, password } -> respuesta: { success: true, user, token }
	- `POST /api/auth/login` - payload: { email, password } -> respuesta: { success: true, user, token }
	- `GET /api/auth/verify` - headers: Authorization: Bearer <token> -> 200 OK si válido

## 2. Empresas
- Archivos: `backend/controllers/empresasController.js`, `api/empresas/*`, `frontend/*` (gestión UI)
- Endpoints:
	- `GET /api/empresas` -> [ { id, nombre, locacion, ... } ]
	- `POST /api/empresas` -> payload: { nombre, locacion, ... } -> { id }
	- `PUT /api/empresas/:id` -> payload: { ... } -> { success }
	- `DELETE /api/empresas/:id` -> { success }

## 3. Herramientas
- Archivos: `backend/controllers/herramientasController.js`, `api/herramientas/*`
- Endpoints similares a Empresas. Salidas incluyen `herramienta.empresa_nombre` cuando hay join.

### Ejemplos de respuestas (extractos desde el entorno de pruebas)
```markdown
# Documentación por Módulo y Componentes

Este documento describe los módulos del proyecto FERDATA, los archivos clave, endpoints y notas de uso.

Formato por módulo:
- Nombre del módulo
- Componentes (archivos relevantes)
- Endpoints / rutas
- Entradas (payloads) y salidas (JSON)
- Notas y casos de prueba

## 1. Autenticación (Auth)
- Archivos: `backend/routes/auth.js`, `backend/controllers/authController.js`, `api/auth/*`, `frontend/js/auth.js`, `frontend/MODULO_INICIO_DE_SESION.html`, `frontend/MODULO_REGISTRO.html`
- Endpoints:
		- `POST /api/auth/register` - payload: { nombre_completo, email, password } -> respuesta: { success: true, user, token }
		- `POST /api/auth/login` - payload: { email, password } -> respuesta: { success: true, user, token }
		- `GET /api/auth/verify` - headers: Authorization: Bearer <token> -> 200 OK si válido

## 2. Empresas
- Archivos: `backend/routes/empresas.js`, `backend/controllers/empresasController.js`, `api/empresas/*`, frontend UI in `frontend/MODULO_CONTROL_DE_MANDO.html`
- Endpoints:
		- `GET /api/empresas` -> [ { id, nombre, locacion, ... } ]
		- `POST /api/empresas` -> payload: { nombre, locacion, ... } -> { id }
		- `PUT /api/empresas/:id` -> payload: { ... } -> { success }
		- `DELETE /api/empresas/:id` -> { success }

## 3. Herramientas
- Archivos: `backend/routes/herramientas.js`, `backend/controllers/herramientasController.js`, `api/herramientas/*`
- Endpoints similares a Empresas. Salidas suelen incluir `herramienta.empresa_nombre` cuando hay joins.

## 4. Inspecciones
- Archivos: `backend/routes/inspecciones.js`, `backend/controllers/inspeccionesController.js`, `api/inspecciones/*`, frontend handlers in `MODULO_CONTROL_DE_MANDO.html`
- Endpoints:
		- `GET /api/inspecciones` -> listado
		- `GET /api/inspecciones/get/:id` (or `/api/inspecciones/:id`) -> detalle de inspección
		- `POST /api/inspecciones` -> crear (PHP fallback: `api/inspecciones/create.php`)

Respuesta de `GET /api/inspecciones/:id` (extracto):
```json
{
	"id": 4,
	"empresa_id": 7,
	"herramienta_id": 11,
	"fecha": "2025-09-01T05:00:00.000Z",
	"inspector": "JOSE FERRER",
	"empresa_nombre": "GMS",
	"herramienta_nombre": "TUBING"
}
```

## 5. Actividades
- Archivos: `backend/routes/actividades.js`, `backend/controllers/actividadesController.js`, `api/actividades/*`
- Endpoint principal: `GET /api/actividades` (puede devolver shapes envueltos `{ value: [...] }` en algunas rutas PHP/legacy)

Ejemplo (extracto):
```json
{
	"id": 6,
	"fecha": "2025-09-01T05:00:00.000Z",
	"empresa": 7,
	"observaciones": "TODA READY",
	"cliente_nombre": "GMS"
}
```

## 6. Usuarios
- Archivos: `backend/routes/usuarios.js`, `backend/controllers/usuariosController.js`, `api/usuarios/*`, `scripts/export_usuarios.js`
- Endpoints:
		- `GET /api/usuarios/read` -> array de usuarios
		- `GET /api/usuarios/get/:id` -> usuario por id
		- `POST /api/usuarios/create` -> crear usuario
		- `PUT /api/usuarios/update/:id` -> actualizar
		- `DELETE /api/usuarios/delete/:id` -> eliminar

Ejemplo (extracto):
```json
{
	"id": 7,
	"nombre": "JOSE FERRER",
	"email": "ferrero01@hotmail.com",
	"activo": 1,
	"fecha_creacion": "2025-08-31T15:43:18.000Z"
}
```

## 7. Reportes
- Archivos: `backend/routes/reportes.js`, `backend/controllers/reportesController.js`, `api/reportes/*`, frontend report handlers in `MODULO_CONTROL_DE_MANDO.html`
- Funcionalidad: crear reportes, listar, descargar. Node endpoint: `/api/reportes/download/:id`. PHP fallback: `api/reportes/download.php`.

## 8. Estadísticas (Stats / Dashboard)
- Archivos: `backend/routes/stats.js`, `backend/controllers/statsController.js` (or inline in routes), `frontend/js/dashboard.js`, `frontend/MODULO_CONTROL_DE_MANDO.html`
- Endpoint: `GET /api/stats` -> { empresas, herramientas, inspecciones, reportes, actividades, dbName }

Ejemplo:
```json
{
	"empresas": 4,
	"herramientas": 6,
	"inspecciones": 1,
	"reportes": 1,
	"actividades": 5,
	"dbName": "magnatesting_db"
}
```

## 9. Frontend (páginas / módulos)
- `frontend/MODULO_PRINCIPAL.html` - página principal
- `frontend/MODULO_INICIO_DE_SESION.html` - login
- `frontend/MODULO_REGISTRO.html` - registro
- `frontend/MODULO_CONTROL_DE_MANDO.html` - panel de administración (usa la mayor parte de endpoints)
- `frontend/css/`, `frontend/js/` - assets y helpers (`frontend/js/auth.js`, `frontend/js/dashboard.js`)

## 10. Scripts y utilidades
- `scripts/create_release.ps1` - empaqueta release en `releases/`
- `scripts/export_db_no_mysqldump.js` - exporta tablas a JSON cuando `mysqldump` no está disponible
- `scripts/generate_sql_inserts_from_json.js` - genera SQL a partir de los JSON exportados
- `scripts/smoke_tests.js` - comparador de endpoints contra `deliverables/` (guarda `tmp/smoke_results.json`)
- `scripts/export_usuarios.js` - helper para exportar usuarios a `deliverables/data_usuarios.json`

## 11. Releases y deliverables
- `releases/ferdata-<version>/` - release empaquetada con `frontend/`, `backend/`, `db_exports/` y `ferdata-db-dump.sql`
- `deliverables/` - JSON exportados usados como referencia para pruebas y documentación

## 12. Base de datos y migraciones
- `database/migrations/` - contiene migrations SQL, por ejemplo `003_create_reportes_table.sql`.
- Nota: si `mysqldump` no está disponible, usar los scripts en `scripts/` para exportar y reconstruir SQL.

## 13. Compatibilidad PHP / Node
- El proyecto soporta dos modos:
		1) Node.js (Express) montado en `/api/*` — preferido para desarrollo (archivos en `backend/`)
		2) PHP (Apache) en `api/` — scripts legacy como `api/empresas/read.php` mantienen compatibilidad con instalaciones Apache+PHP
- El frontend intenta primero los endpoints Node y usa PHP como fallback cuando detecta errores `Cannot GET` o 404.

## 14. Pruebas rápidas
- Ejecutar `node scripts/smoke_tests.js` desde la raíz para verificar endpoints básicos contra `deliverables/`.
- Ver `tmp/smoke_results.json` para resultados.


