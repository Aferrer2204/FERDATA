// Función para mostrar mensajes
function showMessage(text, type, elementId = 'message') {
    const messageEl = document.getElementById(elementId);
    if (!messageEl) return;

    messageEl.textContent = text;
    messageEl.className = `message ${type}`;
    messageEl.style.display = 'block';

    setTimeout(() => {
        messageEl.style.display = 'none';
    }, 5000);
}

// Función para manejar el registro
async function handleRegister(e) {
    e.preventDefault();

    const nombre_completo = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        showMessage('Las contraseñas no coinciden', 'error');
        return;
    }

    try {
        const response = await fetch('http://localhost:3000/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nombre_completo, email, password })
        });

        const data = await response.json();

        if (data.success) {
            showMessage('¡Registro exitoso! Redirigiendo...', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        showMessage('Error de conexión: ' + error.message, 'error');
    }
}

// Función para manejar el login
async function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Disable submit button and show connecting label
    const loginForm = document.getElementById('loginForm');
    let submitBtn = null;
    if (loginForm) submitBtn = loginForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.origText = submitBtn.textContent; submitBtn.textContent = 'Conectando...'; }

    try {
        showMessage('Conectando...', 'info');
        console.info('handleLogin: attempting login for', email);

        // Try relative path first (works when served from Node on :3000)
        let response;
        try {
            response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
        } catch (relErr) {
            console.warn('handleLogin: relative fetch failed, will try explicit Node URL', relErr);
            // Fallback to explicit Node URL if relative fails (useful when page served by Apache)
            response = await fetch('http://localhost:3000/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
        }

        // Be defensive: servers can sometimes return HTML error pages instead of JSON.
        const contentType = response.headers.get('content-type') || '';
        let data;
        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // fallback: read text and show it as error
            const text = await response.text();
            showMessage('Error de conexión: servidor respondió con HTML/texto: ' + (text && text.substring(0, 500)), 'error');
            return;
        }

        if (data && data.success) {
            // Guardar token y información del usuario
            localStorage.setItem('token', data.token);
            const normalizedUser = Object.assign({}, data.user);
            normalizedUser.nombre = normalizedUser.nombre || normalizedUser.nombre_completo || normalizedUser.nombre;
            localStorage.setItem('user', JSON.stringify(normalizedUser));

            showMessage('Autenticación satisfactoria', 'success');
            console.info('handleLogin: success, redirecting', normalizedUser);
            // If a post-login redirect was requested (from MODULO_PRINCIPAL), honor it
            const postRedirect = localStorage.getItem('postLoginRedirect');
            const target = postRedirect || 'MODULO_CONTROL_DE_MANDO.html';
            if (postRedirect) localStorage.removeItem('postLoginRedirect');
            setTimeout(() => {
                window.location.href = target;
            }, 800);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('handleLogin error', error);
        showMessage('Error de conexión: ' + error.message, 'error');
    }
    finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            try { submitBtn.textContent = submitBtn.dataset.origText || 'Ingresar'; } catch (e) { submitBtn.textContent = 'Ingresar'; }
        }
    }
}

// Función para cerrar sesión
function logout() {
    // Clear session and set a flag so the main module opens the login modal automatically
    try { localStorage.removeItem('token'); localStorage.removeItem('user'); } catch (e) { /* ignore */ }
    try { localStorage.setItem('showLogin', '1'); localStorage.setItem('postLoginRedirect', 'MODULO_CONTROL_DE_MANDO.html'); } catch (e) { /* ignore */ }
    // Redirect to the main module page where the login UI is presented
    window.location.href = 'MODULO_PRINCIPAL.html';
}

// Verificar autenticación en páginas protegidas
function checkAuth() {
    const token = localStorage.getItem('token');
    const user = localStorage.getItem('user');

    if (!token || !user) {
        window.location.href = '/';
        return null;
    }

    return JSON.parse(user);
}

// Añadir event listeners cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function () {
    // Para el formulario de registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    // Para el formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // Para el botón de logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    // Verificar autenticación en el dashboard
    if (window.location.pathname.includes('CONTROL_DE_MANDO')) {
        const user = checkAuth();
        if (user) {
            document.getElementById('userAvatar').textContent = user.nombre.charAt(0).toUpperCase();
        }
    }
});