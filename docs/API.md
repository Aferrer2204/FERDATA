# API Documentation

## Autenticación

### POST /api/auth/register
Registra un nuevo usuario

Body:
{
  "nombre_completo": "Nombre Apellido",
  "email": "usuario@ejemplo.com",
  "password": "contraseñaSegura123"
}

### POST /api/auth/login
Inicia sesión

Body:
{
  "email": "usuario@ejemplo.com",
  "password": "contraseñaSegura123"
}

## Dashboard

### GET /api/dashboard
Obtiene estadísticas y datos del dashboard

Headers:
Authorization: Bearer <token>

## Usuarios

### GET /api/usuarios/read
Devuelve la lista de usuarios registrados (Node API). Respuesta: array de objetos con campos clave.

Ejemplo de respuesta actual (extraído de la base de datos):

| id | nombre | email | activo | fecha_creacion |
|---:|--------|-------|:------:|----------------|
| 7  | JOSE FERRER | ferrero01@hotmail.com | 1 | 2025-08-31T15:43:18.000Z |
| 8  | Verify Run | verify_run_20250831111632@example.com | 0 | 2025-08-31T16:16:33.000Z |
| 11 | franklin m | frankli@ejemplo.com | 0 | 2025-08-31T22:19:11.000Z |
| 12 | URIEL URREA | URIEL@EXAMPLE.COM | 0 | 2025-09-01T11:37:50.000Z |
| 13 | janner castañeda | janner@gmail.com | 0 | 2025-09-01T15:15:05.000Z |
| 14 | Tester | test@example.com | 0 | 2025-09-01T17:20:37.000Z |

### GET /api/usuarios/get/:id
Devuelve un usuario por id. Ejemplo: `/api/usuarios/get/7` → objeto usuario.

### POST /api/usuarios/create
Crea un nuevo usuario. Body: { nombre, email, password, rol? }

### PUT /api/usuarios/update/:id
Actualiza un usuario. Body: { nombre?, email?, password?, rol?, activo? }

### DELETE /api/usuarios/delete/:id
Elimina un usuario por id.

### Ejemplo completo (JSON)
La respuesta completa de `GET /api/usuarios/read` (array de objetos) en el entorno actual:

```json
[
  {
    "id": 7,
    "nombre": "JOSE FERRER",
    "email": "ferrero01@hotmail.com",
    "rol": null,
    "activo": 1,
    "fecha_creacion": "2025-08-31T15:43:18.000Z"
  },
  {
    "id": 8,
    "nombre": "Verify Run",
    "email": "verify_run_20250831111632@example.com",
    "rol": null,
    "activo": 0,
    "fecha_creacion": "2025-08-31T16:16:33.000Z"
  },
  {
    "id": 11,
    "nombre": "franklin m",
    "email": "frankli@ejemplo.com",
    "rol": null,
    "activo": 0,
    "fecha_creacion": "2025-08-31T22:19:11.000Z"
  },
  {
    "id": 12,
    "nombre": "URIEL URREA",
    "email": "URIEL@EXAMPLE.COM",
    "rol": null,
    "activo": 0,
    "fecha_creacion": "2025-09-01T11:37:50.000Z"
  },
  {
    "id": 13,
    "nombre": "janner castañeda",
    "email": "janner@gmail.com",
    "rol": null,
    "activo": 0,
    "fecha_creacion": "2025-09-01T15:15:05.000Z"
  },
  {
    "id": 14,
    "nombre": "Tester",
    "email": "test@example.com",
    "rol": null,
    "activo": 0,
    "fecha_creacion": "2025-09-01T17:20:37.000Z"
  }
]
```

### Ejemplos de solicitud (curl / PowerShell)

1) Obtener todos los usuarios (GET):

curl:

```bash
curl -s -X GET "http://localhost:3000/api/usuarios/read"
```

PowerShell:

```powershell
Invoke-RestMethod -Uri 'http://localhost:3000/api/usuarios/read' -Method GET
```

2) Obtener un usuario por id (GET):

curl:

```bash
curl -s -X GET "http://localhost:3000/api/usuarios/get/7"
```

PowerShell:

```powershell
Invoke-RestMethod -Uri 'http://localhost:3000/api/usuarios/get/7' -Method GET
```

3) Crear un usuario (POST) — ejemplo (no incluye hashing en cliente):

curl:

```bash
curl -s -X POST "http://localhost:3000/api/usuarios/create" \
  -H 'Content-Type: application/json' \
  -d '{"nombre":"Nuevo Usuario","email":"nuevo@ejemplo.com","password":"MiPass123"}'
```

PowerShell:

```powershell
$body = @{ nombre = 'Nuevo Usuario'; email = 'nuevo@ejemplo.com'; password = 'MiPass123' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://localhost:3000/api/usuarios/create' -Method Post -Body $body -ContentType 'application/json'
```

Nota: los endpoints Node usan rutas montadas en `/api/usuarios`. Si estás ejecutando la versión PHP (Apache) los scripts equivalentes están en `api/usuarios/` (por ejemplo `api/usuarios/read.php`).