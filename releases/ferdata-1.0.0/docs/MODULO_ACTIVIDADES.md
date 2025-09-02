# Módulo: Actividades

## Descripción
Gestiona el registro diario de actividades (crear, leer, actualizar, eliminar).

## Endpoints / Interfaces
- GET /api/actividades/read.php -> listar
- POST /api/actividades/create.php -> crear
- PUT /api/actividades/update.php -> actualizar
- DELETE /api/actividades/delete.php -> eliminar

## Datos de entrada (ejemplo)
POST /api/actividades/create.php
```
{
  "tipo": "mantenimiento",
  "descripcion": "Revisión semanal",
  "fecha": "2025-09-01",
  "empresa_id": 12,
  "avance": 10
}
```

## Datos de salida (ejemplo)
```
{ "message": "Actividad creada correctamente", "id": 42 }
```

## Errores comunes
- 400: campos obligatorios faltan
- 500: error en base de datos

## Notas de implementación
- Soporta esquemas legacy y modernos (ver `api/tmp_db_info.php`)
