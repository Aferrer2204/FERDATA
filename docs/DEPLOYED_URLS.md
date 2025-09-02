# URLs desplegadas (entornos reales / locales)

Este archivo lista las URL reales donde el proyecto FERDATA está desplegado o puede consultarse localmente.
Actualiza las filas con branch/tag/commit/responsable cuando realices despliegues públicos.

| Entorno | Módulo | URL / Ruta | Branch/Tag | Commit | Fecha | Responsable |
|---|---:|---|---|---|---|---|
| Local (Node) | Frontend (Control de Mando) | http://localhost:3000/ | main | ver repo | 2025-09-01 | Equipo FERDATA |
| Local (Node) | API (Express) | http://localhost:3000/api | main | ver repo | 2025-09-01 | Equipo FERDATA |
| Local (XAMPP / Apache) | Frontend (PHP/static) | http://localhost/FERDATA/frontend/MODULO_CONTROL_DE_MANDO.html | n/a | n/a | 2025-09-01 | Equipo FERDATA |
| Local (XAMPP / Apache) | API (PHP scripts) | http://localhost/FERDATA/api/ (ej: /FERDATA/api/empresas/read.php) | n/a | n/a | 2025-09-01 | Equipo FERDATA |
| Release (paquete) | Release v1.0.0 (artifacts) | file:///c:/xampp/xampp/FERDATA/releases/ferdata-1.0.0/ | v1.0.0 | ver repo | 2025-09-01 | Equipo FERDATA |

Notas:

- El servidor Node del backend se ejecuta por defecto en el puerto 3000 en este repositorio; muchas llamadas del frontend usan `http://localhost:3000`.
- Como fallback para instalaciones con Apache+PHP (XAMPP), los scripts PHP están bajo la carpeta `api/` y se exponen típicamente en `http://localhost/FERDATA/api/` cuando la carpeta del proyecto está servida desde Apache.
- Para ver el contenido del release empaquetado abra la ruta local `releases/ferdata-1.0.0/` en el explorador de archivos o extraiga el zip.


