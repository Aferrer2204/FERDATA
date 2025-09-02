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