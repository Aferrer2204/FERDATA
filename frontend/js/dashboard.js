// Función para cargar datos del dashboard
async function loadDashboardData() {
    try {
        const token = localStorage.getItem('token');
        const response = await fetch('http://localhost:3000/api/dashboard', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar estadísticas
            document.getElementById('empresas-count').textContent = data.empresas;
            document.getElementById('herramientas-count').textContent = data.herramientas;
            document.getElementById('inspecciones-count').textContent = data.inspecciones;
            document.getElementById('reportes-count').textContent = data.reportes;
            document.getElementById('actividades-count').textContent = data.actividades;

            // Not loading recent lists here — dashboard shows only statistics now
        } else {
            console.error('Error al cargar datos del dashboard:', data.message);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
    }
}

// Recent items removed: dashboard only shows stats now.

// Cargar datos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.pathname.includes('CONTROL_DE_MANDO')) {
        loadDashboardData();
    }
});