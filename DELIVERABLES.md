# Paquete de Entrega - FERDATA

Este archivo lista los artefactos a entregar y su ubicación en el repositorio.

Requerimientos solicitados y dónde encontrarlos:

- Repositorio de control de versiones: usar Git (instrucciones en `CONTRIBUTING.md`).
- Archivos ejecutables / paquetes: generador en `scripts/create_release.ps1`; ejemplo de release en `releases/` (no incluido en repo hasta generar).
- URLs desplegadas: `docs/DEPLOYED_URLS.md` (mantener actualizada con host y branch/commit).
- Documentación por módulo/componente: `docs/MODULES.md`, `docs/TECHNICAL_MANUAL.md`, `docs/TECHNICAL_MANUAL_FULL.md`.
- Pruebas realizadas: `docs/TEST_REPORTS.md` (añadir casos y resultados) y `backend/logs/` para evidencia.
- Configuración de servidores/DB/ambientes: `docs/DEPLOYMENT_DETAILS.md`.
- Manuales técnicos: `docs/TECHNICAL_MANUAL_FULL.md` y `docs/TECHNICAL_MANUAL.md`.

Pasos recomendados para entrega final:
1. Actualizar `docs/DEPLOYED_URLS.md` con URLs reales, branch y commit.
2. Ejecutar `.	emplatesuild_release.ps1` o `scripts/create_release.ps1` para generar `releases/ferdata-<vers>.zip`.
3. Comprimir y firmar el release si es necesario, y subir al repositorio (tag + release) o a un artefacto compartido.
4. Adjuntar `docs/TEST_REPORTS.md` con evidencias y logs.
