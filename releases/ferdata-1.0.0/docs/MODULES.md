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

## 4. Inspecciones
- Archivos: `backend/controllers/inspeccionesController.js`, `api/inspecciones/*`, frontend handlers
- GET list: `GET /api/inspecciones` -> array con campos: id, empresa_id, empresa_nombre, herramienta_id, herramienta_nombre, fecha, resultado, inspector, observaciones
- GET single: `GET /api/inspecciones/:id` -> object con same fields

## 5. Actividades
- Archivos: `backend/controllers/actividadesController.js`, `api/actividades/*`
- GET list: `GET /api/actividades` -> may return wrapped shapes; see `docs/TEST_REPORTS.md` for examples; fields include id, fecha, empresa_id, cliente_nombre (legacy), descripcion, usuario_nombre, avance

---
Mantener esta página por módulo con ejemplos reales (copiar payloads y respuestas desde DevTools para mayor fidelidad).
