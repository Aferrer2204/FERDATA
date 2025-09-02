# Contribuciones y Control de Versiones

Se recomienda usar Git como control de versiones. Ejemplo de flujo de trabajo:

1. Clonar repo: `git clone <repo.git>`
2. Crear branch para la tarea: `git checkout -b feat/<descripcion>`
3. Hacer commits atómicos y descriptivos: `git commit -m "feat: añadir carga de actividades"`
4. Push al remoto: `git push origin feat/<descripcion>`
5. Crear Pull Request y pedir revisión.

Tags y releases:
- Use `git tag -a v1.0.0 -m "Release 1.0.0"` y luego `git push --tags`.

Entregables para auditoría:
- Incluir `releases/ferdata-<version>.zip`, `docs/DEPLOYED_URLS.md`, `docs/TEST_REPORTS.md`.
