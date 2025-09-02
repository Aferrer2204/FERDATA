const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const authRoutes = require('./routes/auth');
const empresasRoutes = require('./routes/empresas');
const herramientasRoutes = require('./routes/herramientas');
const actividadesRoutes = require('./routes/actividades');
const inspeccionesRoutes = require('./routes/inspecciones');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, '../frontend')));

// Simple request logger to a file to help debug connectivity
const fs = require('fs');
const logsDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logsDir)) fs.mkdirSync(logsDir, { recursive: true });
const logFile = path.join(logsDir, 'server.log');
function writeLog(line) {
    try {
        fs.appendFileSync(logFile, `${new Date().toISOString()} ${line}\n`);
    } catch (e) {
        console.error('Failed to write log:', e);
    }
}

app.use((req, res, next) => {
    const start = Date.now();
    writeLog(`REQ ${req.method} ${req.originalUrl} from ${req.ip}`);
    res.on('finish', () => {
        const duration = Date.now() - start;
        writeLog(`RES ${res.statusCode} ${req.method} ${req.originalUrl} ${duration}ms`);
    });
    next();
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok' });
});

// ...existing code...

// Depuración: verificar qué devuelve cada archivo de rutas
console.log('authRoutes:', authRoutes);
console.log('empresasRoutes:', empresasRoutes);
console.log('herramientasRoutes:', herramientasRoutes);
console.log('actividadesRoutes:', actividadesRoutes);

// Rutas
app.use('/api/auth', authRoutes);
app.use('/api/empresas', empresasRoutes);
app.use('/api/herramientas', herramientasRoutes);
app.use('/api/actividades', actividadesRoutes);
app.use('/api/inspecciones', inspeccionesRoutes);

// Servir archivos HTML
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, '../frontend/MODULO_PRINCIPAL.html'));
});

app.get('/login', (req, res) => {
    res.sendFile(path.join(__dirname, '../frontend/MODULO_INICIO_DE_SESION.html'));
});

app.get('/registro', (req, res) => {
    res.sendFile(path.join(__dirname, '../frontend/MODULO_REGISTRO.html'));
});

app.get('/dashboard', (req, res) => {
    res.sendFile(path.join(__dirname, '../frontend/MODULO_CONTROL_DE_MANDO.html'));
});

app.listen(PORT, () => {
    console.log(`Servidor corriendo en puerto ${PORT}`);
});

const statsRoutes = require('./routes/stats');
app.use('/api/stats', statsRoutes);

// Proxy: reenviar llamadas a la API PHP en Apache cuando la ruta no esté implementada en Node
// Nota: deshabilitado temporalmente para depuración porque en algunos entornos puede interferir.
/*
const { createProxyMiddleware } = require('http-proxy-middleware');
const APACHE_API = process.env.APACHE_API || 'http://localhost/FERDATA/api';

// Middleware que intenta pasar la petición a las rutas Node; si no hay respuesta (404), usa proxy hacia Apache
app.use('/api', (req, res, next) => {
    // marcar para detectar 404
    res.locals._proxyFallback = true;
    next();
});

app.use('/api', createProxyMiddleware({
    target: APACHE_API,
    changeOrigin: true,
    logLevel: 'warn',
    onProxyReq: (proxyReq, req, res) => {
        if (req.body && Object.keys(req.body).length) {
            const bodyData = JSON.stringify(req.body);
            proxyReq.setHeader('Content-Type', 'application/json');
            proxyReq.setHeader('Content-Length', Buffer.byteLength(bodyData));
            proxyReq.write(bodyData);
        }
    }
}));
*/

// Error handler: ensure API routes always return JSON errors (avoid HTML pages)
app.use((err, req, res, next) => {
    try {
        writeLog(`ERROR ${err && err.stack ? err.stack : err}`);
    } catch (e) { console.error('Failed to write error log', e); }
    if (req.originalUrl && req.originalUrl.startsWith('/api')) {
        res.status(err.status || 500).json({ success: false, message: err.message || 'Internal Server Error' });
    } else {
        // default express behavior for non-api routes
        next(err);
    }
});

