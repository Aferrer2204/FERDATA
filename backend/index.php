const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const authRoutes = require('./routes/auth');
const empresasRoutes = require('./routes/empresas');
const herramientasRoutes = require('./routes/herramientas');
const actividadesRoutes = require('./routes/actividades');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, '../frontend')));

// Rutas API
app.use('/api/auth', authRoutes);
app.use('/api/empresas', empresasRoutes);
app.use('/api/herramientas', herramientasRoutes);
app.use('/api/actividades', actividadesRoutes);

// Rutas de frontend
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

// Manejo de error 404
app.use((req, res) => {
    res.status(404).sendFile(path.join(__dirname, '../frontend/404.html'));
});

app.listen(PORT, () => {
    console.log(`✅ Servidor corriendo en puerto ${PORT}`);
});
