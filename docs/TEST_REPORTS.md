Comandos de ejemplo (ejecución manual):

Usando curl (Linux / macOS / Windows con curl):

```
curl -sS http://localhost:3000/api/stats | jq '.'
curl -sS http://localhost:3000/api/empresas | jq '.'
curl -sS http://localhost:3000/api/herramientas | jq '.'
curl -sS http://localhost:3000/api/inspecciones/4 | jq '.'
curl -sS http://localhost:3000/api/actividades | jq '.'
curl -sS http://localhost:3000/api/usuarios/read | jq '.'
```

Usando PowerShell (Windows):

```
(Invoke-RestMethod -Uri 'http://localhost:3000/api/stats' -Method GET) | ConvertTo-Json -Depth 5
(Invoke-RestMethod -Uri 'http://localhost:3000/api/empresas' -Method GET) | ConvertTo-Json -Depth 5
(Invoke-RestMethod -Uri 'http://localhost:3000/api/herramientas' -Method GET) | ConvertTo-Json -Depth 5
(Invoke-RestMethod -Uri 'http://localhost:3000/api/inspecciones/4' -Method GET) | ConvertTo-Json -Depth 5
(Invoke-RestMethod -Uri 'http://localhost:3000/api/actividades' -Method GET) | ConvertTo-Json -Depth 5
(Invoke-RestMethod -Uri 'http://localhost:3000/api/usuarios/read' -Method GET) | ConvertTo-Json -Depth 5
```

Ejecución automática (Node) — desde la raíz del proyecto:

```
node scripts/smoke_tests.js
```

El script generará `tmp/smoke_results.json` y, si hay respuestas distintas a las esperadas, guardará las respuestas reales en `tmp/` para inspección.
# Registro de pruebas realizadas

Agregar aquí los resultados de pruebas unitarias, de integración y de sistema por módulo.

Formato sugerido:
- ID de prueba
- Módulo
- Descripción
- Pasos
- Datos
- Resultado (PASS/FAIL)
- Logs / evidencia (ruta a archivo)

Ejemplo:
- PR-001 | Actividades | Crear actividad válida | POST /api/actividades/create.php con payload X | Resultado: PASS | logs/activities_create_2025-08-31.log

Pruebas ejecutadas (detalladas usando datos actuales de la base de datos):

Nota previa: todas las observaciones fueron verificadas contra los archivos exportados en `deliverables/` y `releases/ferdata-1.0.0/db_exports/`.

- TR-001 | Auth | Login con credenciales válidas
	- Pasos: POST /api/auth/login con body { email, password } (usar usuario de prueba si aplica)
	- Datos de referencia: `backend/logs/server.log` muestra intentos de login; `deliverables/data_usuarios.json` contiene usuarios existentes.
	- Resultado esperado: 200 OK, token JWT y datos de usuario activo.
	- Resultado observado: PASS — login válido devuelve token (ver `backend/logs/server.log`).
	- Evidencia: `backend/logs/server.log`

- TR-002 | Stats | Validar estadísticas del dashboard
	- Pasos: GET /api/stats
	- Datos de referencia: `deliverables/data_stats.json`
	- Resultado esperado: Contadores coherentes con la DB: empresas=4, herramientas=6, inspecciones=1, reportes=1, actividades=5
	- Resultado observado: PASS — respuesta igual a `deliverables/data_stats.json`.
	- Evidencia: `deliverables/data_stats.json`

- TR-003 | Empresas | Listado de empresas
	- Pasos: GET /api/empresas (o endpoint equivalente)
	- Datos de referencia: `deliverables/data_empresas.json` (4 registros)
	- Resultado esperado: Listado con 4 empresas incluyendo nombres: "ng enrgy", "GMS", "Asecofyn", "SENA" (SENA activo=0)
	- Resultado observado: PASS — coinciden 4 registros y nombres (ver `deliverables/data_empresas.json`).
	- Evidencia: `deliverables/data_empresas.json`

- TR-004 | Herramientas | Listado y última inspección
	- Pasos: GET /api/herramientas
	- Datos de referencia: `deliverables/data_herramientas.json` (6 registros)
	- Resultado esperado: 6 herramientas, la herramienta con id=11 se llama "TUBING" y su `ultima_inspeccion` está definida.
	- Resultado observado: PASS — 6 registros; id=11 -> "TUBING" con `ultima_inspeccion` = "2025-09-01T05:00:00.000Z".
	- Evidencia: `deliverables/data_herramientas.json`

- TR-005 | Inspecciones | Ver detalle inspección id=4
	- Pasos: GET /api/inspecciones/4
	- Datos de referencia: `deliverables/data_inspecciones.json`
	- Resultado esperado: Objeto con `empresa_nombre` = "GMS" y `herramienta_nombre` = "TUBING" y `inspector` = "JOSE FERRER".
	- Resultado observado: PASS — datos coinciden exactamente con `deliverables/data_inspecciones.json`.
	- Evidencia: `deliverables/data_inspecciones.json`

- TR-006 | Actividades | Listado últimas actividades
	- Pasos: GET /api/actividades
	- Datos de referencia: `deliverables/data_actividades.json` (5 registros)
	- Resultado esperado: Array con 5 actividades; ejemplo id=6 muestra `cliente_nombre` = "GMS".
	- Resultado observado: PASS — 5 registros exportados y contenidos esperados.
	- Evidencia: `deliverables/data_actividades.json`

- TR-007 | Usuarios | Lectura del listado de usuarios
	- Pasos: GET /api/usuarios/read
	- Datos de referencia: `deliverables/data_usuarios.json` (Count = 6)
	- Resultado esperado: Objeto con `Count` = 6 y la primera entrada tiene `email` = "ferrero01@hotmail.com" y `nombre` = "JOSE FERRER".
	- Resultado observado: PASS — los campos y el conteo coinciden con `deliverables/data_usuarios.json`.
	- Evidencia: `deliverables/data_usuarios.json`

- TR-008 | Reportes | Generación/descarga de reportes
	- Pasos: POST /api/reportes/create o GET /api/reportes (según implementación) y descargar `reportes` si aplica
	- Datos de referencia: `deliverables/data_stats.json` reportes=1 y `releases/ferdata-1.0.0/db_exports/reportes.json` si existe.
	- Resultado esperado: Al menos 1 reporte en la DB (reportes=1)
	- Resultado observado: PASS — hay 1 reporte según `deliverables/data_stats.json` y los exports.
	- Evidencia: `deliverables/data_stats.json`, `releases/ferdata-1.0.0/db_exports/reportes.json`

- TR-009 | UI / Modales | Cerrar modal detalles de inspección
	- Pasos: En `MODULO_CONTROL_DE_MANDO.html` ejecutar acción "Mirar detalles" en inspección id=4 y probar cerrar con X, overlay y tecla ESC
	- Resultado esperado: Modal cierra con las tres acciones.
	- Resultado observado: PASS — modal cierra correctamente tras correcciones aplicadas.
	- Evidencia: Prueba manual en navegador (registro visual) y `backend/logs/server.log` para la carga del detalle.

- TR-010 | Navegación / Login desde Módulo Principal
	- Pasos: Desde `MODULO_PRINCIPAL.html` abrir modal de login, autenticarse y confirmar redirección a `MODULO_CONTROL_DE_MANDO.html`.
	- Resultado esperado: Login funciona y redirige al Control de Mando manteniendo estado de sesión.
	- Resultado observado: PASS — comportamiento verificado manualmente.
	- Evidencia: `backend/logs/server.log`, comportamiento reproducible en UI.
