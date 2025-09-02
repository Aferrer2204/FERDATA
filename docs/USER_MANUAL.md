# Manual de Usuario — FERDATA

Este manual explica las operaciones diarias del sistema FERDATA desde la perspectiva de un usuario final y administrador ligero. Incluye: acceso, navegación, uso de los módulos principales (Control de Mando, Empresas, Herramientas, Inspecciones, Actividades, Reportes, Usuarios), generación y descarga de reportes, y resolución de problemas frecuentes.

---

## 1. Acceso y registro

### 1.1 Abrir la aplicación
- Si ejecuta el backend Node localmente: abra http://localhost:3000/
- Si usa XAMPP/Apache: abra `http://localhost/FERDATA/frontend/MODULO_PRINCIPAL.html`

![Pantalla de inicio de sesión](./images/login.png)
*Figura: formulario de inicio de sesión*

### 1.2 Registro (nuevo usuario)
1. Ir a la página de registro (`MODULO_REGISTRO.html` / menú Registrarse).
2. Completar: Nombre completo, Email, Contraseña.
3. Pulsar "Registrar". Tras registro exitoso, el sistema puede intentar iniciar sesión automáticamente.

### 1.3 Iniciar sesión
1. Ir a `MODULO_INICIO_DE_SESION.html` o pulsar "Iniciar sesión".
2. Introducir Email y Contraseña.
3. Al iniciar sesión correctamente el frontend guarda un token en localStorage y redirige al Control de Mando (Dashboard).

---

## 2. Control de Mando (Dashboard)

El `MODULO_CONTROL_DE_MANDO.html` es el panel principal para administradores y operadores.

![Dashboard](./images/dashboard.png)
*Figura: vista general del Control de Mando (estadísticas y accesos rápidos)*

Funciones clave:
- Visualizar estadísticas globales: empresas registradas, herramientas, inspecciones realizadas, reportes generados, actividades.
- Acceso rápido a listados: Empresas, Herramientas, Inspecciones, Actividades, Usuarios.
- Crear / editar / eliminar elementos (según permisos del usuario).
- Descargar reportes generados.

Uso típico:
1. Tras iniciar sesión, si no se redirige automáticamente, abrir `/dashboard` o `http://localhost:3000/dashboard`.
2. Revisar widget de estadísticas (debe coincidir con la base de datos; si no, recargar la página).
3. Usar los botones para ver listados y abrir modal de detalle (por ejemplo: inspecciones -> "Mirar detalles").

Nota de UX: el frontend intenta consumir los endpoints Node en `/api/*`; si el servidor Node no responde, puede caer hacia los scripts PHP en `api/` como fallback.

---

## 3. Módulo Empresas

![Empresas](./images/empresas.png)
*Figura: listado de empresas y formulario de creación/edición*

Acciones comunes:
- Listar empresas: desde Control de Mando o visitar `/api/empresas`.
- Crear empresa: botón "Nueva empresa" -> completar formulario -> Guardar.
- Editar empresa: abrir la fila y pulsar "Editar" -> guardar cambios.
- Borrar empresa: botón "Eliminar" (confirmar en modal).

Campos típicos: id, nombre, locacion, contacto, teléfono, correo.

---

## 4. Módulo Herramientas

Acciones comunes:
- Listar herramientas, Crear, Editar, Eliminar.
- Asociar herramienta a empresa si aplica.

Campos típicos: id, nombre, código, empresa_id, estado, fecha_registro.

---

## 5. Módulo Inspecciones

![Inspecciones](./images/inspecciones.png)
*Figura: vista de inspecciones y modal de detalle*

Acciones comunes:
- Listar inspecciones.
- Ver detalle: abrir inspección y pulsar "Mirar detalles" (modal).
- Crear inspección: completar formulario con empresa, herramienta, fecha, resultado, observaciones.

Problema conocido y solución: si el modal de detalle no se cierra, asegúrese de que no hay errores JS en consola. Refrescar la página suele restablecer el comportamiento; si persiste, revise `frontend/MODULO_CONTROL_DE_MANDO.html` para asegurarse de que el listener del botón de cierre existe.

---

## 6. Módulo Actividades

Acciones comunes:
- Listado de actividades, crear nuevas actividades asociadas a empresas/herramientas y asignar avance.
- Campos típicos: id, fecha, empresa_id, descripcion, usuario_responsable, avance (%).

---

## 7. Módulo Reportes

![Reportes](./images/reportes.png)
*Figura: lista de reportes y opciones de descarga*

Funcionalidad:
- Crear reportes desde el panel (según filtros y datos disponibles).
- Listar reportes generados y descargar (PDF o CSV según configuración).

Descarga: al seleccionar "Descargar reporte" el frontend solicitará `/api/reportes/download/:id` o, si está en PHP fallback, el script `api/reportes/download.php`.

---

## 8. Módulo Usuarios (administrador)

Acciones:
- Listar usuarios: ver `/api/usuarios/read` desde el panel de administración.
- Ver usuario: `/api/usuarios/get/:id`.
- Crear usuario: formulario en UI o `POST /api/usuarios/create`.
- Editar/Eliminar: según permisos.

Nota: la documentación de la API con ejemplos curl/PowerShell se encuentra en `docs/API.md`.

---

## 9. Flujos habituales y ejemplos rápidos

- Crear empresa + crear herramienta + crear inspección para esa herramienta.
- Registrar actividad relacionada a una empresa y asignar progreso.
- Generar y descargar un reporte que incluya inspecciones filtradas por fecha.

---

## 10. Troubleshooting (usuario)

- No puedo iniciar sesión:
  - Verifique email/contraseña.
  - Si el servidor Node corre en otro puerto, edite la configuración del frontend para apuntar al backend correcto.

- Estadísticas no coinciden con la BD:
  - Asegúrese de que la API (`/api/stats`) devuelve los conteos correctos.
  - Ejecutar `node scripts/smoke_tests.js` para comparar la API con `deliverables/`.

- El modal de inspecciones no se cierra:
  - Abrir la consola del navegador (F12) y revisar errores JS.
  - Si hay mensajes `Cannot GET /api/...` el backend Node no está corriendo.

---

## 11. Accesos y permisos

- La aplicación no incluye un gestor avanzado de roles en la UI por defecto; usuarios suelen tener permisos administrativos o de operador según cómo se administren en la base de datos.
- Para operaciones sensibles (borrado masivo de datos) use la interfaz de administración y confirme las operaciones.

---

## 12. Recursos y documentación adicional
- API: `docs/API.md`
- Deployment & DevOps: `docs/DEPLOYMENT_DETAILS.md`
- Tests & QA: `docs/TEST_REPORTS.md` y `scripts/smoke_tests.js`
- Entregables y datos de referencia: `deliverables/` (JSON de ejemplo)



