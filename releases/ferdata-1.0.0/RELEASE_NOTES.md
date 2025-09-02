# Release: ferdata-1.0.0 (updated)

Included fixes and artifacts:

- Fixed dashboard statistics mismatch and duplicate JS declarations.
- Enabled login from main module and post-login redirect to Control de Mando.
- Restored and fixed APIs for empresas, herramientas, actividades, inspecciones.
- Fixed usuarios controller to match DB columns and exposed `/api/usuarios` endpoints in Node.
- Exports: JSON exports for empresas, herramientas, inspecciones, actividades, usuarios, reportes under `db_exports/`.
- Database dump generated from exports as `ferdata-db-dump.sql` (SQL CREATE + INSERT statements).

Notes:
- Original `mysqldump` not found on the host; the SQL dump was generated from exported JSON and schema via `SHOW CREATE TABLE`.
- To import the dump manually, review `ferdata-db-dump.sql` and import into a MySQL server.

Artifacts in this release:
- All source files under project root
- `db_exports/` folder with JSON and schema files
- `ferdata-db-dump.sql`

Verified on: 2025-09-01
