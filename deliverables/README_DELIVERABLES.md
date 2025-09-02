Entrega: FERDATA - Paquete de Entrega

Este directorio contiene la documentación y las referencias necesarias para entregar el código fuente, ejecutables y evidencia asociada del proyecto FERDATA.

Contenido entregable (resumen):
- Código fuente (carpeta raíz del repositorio)
- Ejecutables / paquete de release: generado por `scripts/create_release.ps1` (PowerShell) o `scripts/build_release.ps1` (opcional)
- URLs desplegadas: `docs/DEPLOYED_URLS.md`
- Documentación por módulo y componentes: `docs/MODULES.md` y `docs/` (manuales)
- Pruebas y resultados: `docs/TEST_REPORTS.md`
- Configuraciones de servidores y DB: `docs/DEPLOYMENT_DETAILS.md`
- Manual técnico y operativos: `docs/TECHNICAL_MANUAL.md` y `docs/TECHNICAL_MANUAL_FULL.md`

Cómo generar el paquete de entrega (ejemplo Windows PowerShell):

1. Abrir PowerShell en la raíz del proyecto `C:\xampp\xampp\FERDATA`.
2. Ejecutar:

```powershell
./scripts/create_release.ps1 -Version 1.0.0 -OutputDir .\releases
```

El script empaqueta los siguientes elementos en `releases/ferdata-<version>.zip`:
- `frontend/` (archivos estáticos)
- `backend/` (servidor Node.js y controladores)
- `api/` (endpoints PHP)
- `database/migrations/` y `database/*.sql`
- `docs/` (todos los documentos)
- `scripts/` (scripts de despliegue y build)

Si necesita un binario o instalador específico para Windows/Linux, añada pasos de build en `scripts/` y documente el proceso aquí.

Contacto del responsable de la entrega: revisar `docs/DEPLOYED_URLS.md`.
