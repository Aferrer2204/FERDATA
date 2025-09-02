const express = require('express');
const router = express.Router();
const db = require('../db');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const authMiddleware = require('../middleware/auth');
const fs = require('fs');
const path = require('path');
const logFile = path.join(__dirname, '..', 'logs', 'server.log');
function writeLog(line) {
    try {
        fs.appendFileSync(logFile, `${new Date().toISOString()} AUTH ${line}\n`);
    } catch (e) {
        console.error('AUTH log write failed', e);
    }
}

// Endpoint temporal para crear un usuario de prueba (solo debugging)
router.get('/seed-test-user', async (req, res) => {
    console.log('[DEBUG] seed-test-user entered');
    writeLog('seed-test-user entered');
    try {
        const email = 'test@example.com';
        const plain = 'test';

        // Verificar si existe
        console.log('[DEBUG] running SELECT usuarios');
        writeLog('running SELECT usuarios');
        db.query('SELECT id FROM usuarios WHERE email = ?', [email], async (err, results) => {
            console.log('[DEBUG] SELECT callback', { err: !!err, resultsCount: results && results.length });
            writeLog(`SELECT callback err=${!!err} resultsCount=${results && results.length}`);
            if (err) return res.status(500).json({ success: false, message: 'Error SQL', error: err });
            if (results.length > 0) return res.json({ success: true, message: 'Usuario ya existe', user: { email } });

            console.log('[DEBUG] hashing password');
            writeLog('hashing password');
            const hashed = await bcrypt.hash(plain, 10);
            console.log('[DEBUG] password hashed');
            writeLog('password hashed');
            db.query('INSERT INTO usuarios (nombre_completo, email, password) VALUES (?, ?, ?)', ['Tester', email, hashed], (err2, result) => {
                console.log('[DEBUG] INSERT callback', { err: !!err2, insertId: result && result.insertId });
                writeLog(`INSERT callback err=${!!err2} insertId=${result && result.insertId}`);
                if (err2) return res.status(500).json({ success: false, message: 'Error al crear usuario', error: err2 });
                res.json({ success: true, message: 'Usuario de prueba creado', user: { id: result.insertId, email } });
            });
        });
    } catch (e) {
        res.status(500).json({ success: false, message: 'Error interno', error: e.toString() });
    }
});

router.post('/register', async (req, res) => {
    const { nombre_completo, email, password } = req.body;
    if (!nombre_completo || !email || !password) {
        return res.json({ success: false, message: 'Todos los campos son obligatorios' });
    }

    db.query(
        'SELECT * FROM usuarios WHERE email = ?',
        [email],
        async (err, results) => {
            if (err) {
                console.error('Error SQL:', err);
                writeLog(`register SELECT error: ${err}`);
                return res.json({ success: false, message: 'Error en el servidor', error: err });
            }
            if (results.length > 0) {
                return res.json({ success: false, message: 'El correo ya está registrado' });
            }

            // Hashea la contraseña antes de guardar
            const hashedPassword = await bcrypt.hash(password, 10);

            db.query(
                'INSERT INTO usuarios (nombre_completo, email, password) VALUES (?, ?, ?)',
                [nombre_completo, email, hashedPassword],
                (err, result) => {
                    if (err) {
                        console.error('Error SQL:', err);
                        writeLog(`register INSERT error: ${err}`);
                        return res.json({ success: false, message: 'Error al registrar usuario', error: err });
                    }

                    // Generar token inmediatamente para autologin
                    const user = { id: result.insertId, nombre_completo, email };
                    const token = jwt.sign({ id: user.id, email: user.email }, process.env.JWT_SECRET || 'secreto', { expiresIn: '1h' });

                    res.json({ success: true, message: 'Usuario registrado correctamente', token, user });
                }
            );
        }
    );
});

// Añadir endpoint de login (POST /api/auth/login)
router.post('/login', (req, res) => {
    console.log('[DEBUG] login entered', req.body && { email: req.body.email });
    writeLog(`login entered email=${req.body && req.body.email}`);
    const { email, password } = req.body;
    if (!email || !password) {
        return res.json({ success: false, message: 'Email y contraseña son obligatorios' });
    }

    console.log('[DEBUG] running SELECT usuarios for login');
    writeLog('running SELECT usuarios for login');
    db.query('SELECT * FROM usuarios WHERE email = ?', [email], async (err, results) => {
        console.log('[DEBUG] SELECT login callback', { err: !!err, resultsCount: results && results.length });
        writeLog(`SELECT login callback err=${!!err} resultsCount=${results && results.length}`);
        if (err) {
            console.error('Error SQL:', err);
            writeLog(`SQL error: ${err}`);
            return res.json({ success: false, message: 'Error en el servidor', error: err });
        }

        if (results.length === 0) {
            return res.json({ success: false, message: 'Credenciales incorrectas' });
        }

        const user = results[0];
        console.log('[DEBUG] comparing password');
        writeLog('comparing password');
        const match = await bcrypt.compare(password, user.password);
        console.log('[DEBUG] compare result', match);
        writeLog(`compare result: ${match}`);
        if (!match) {
            writeLog('password mismatch');
            return res.json({ success: false, message: 'Credenciales incorrectas' });
        }

        const token = jwt.sign({ id: user.id, email: user.email }, process.env.JWT_SECRET || 'secreto', { expiresIn: '1h' });

        res.json({ success: true, message: 'Autenticación exitosa', token, user: { id: user.id, nombre_completo: user.nombre_completo, email: user.email } });
        writeLog('login success, token issued');
    });
});

// Un endpoint alternativo que usa db.promise() y async/await para depuración
router.post('/login2', async (req, res) => {
    const { email, password } = req.body;
    writeLog(`login2 entered email=${email}`);
    if (!email || !password) return res.json({ success: false, message: 'Email y contraseña son obligatorios' });
    try {
        const [rows] = await db.promise().execute('SELECT * FROM usuarios WHERE email = ?', [email]);
        if (!rows || rows.length === 0) return res.json({ success: false, message: 'Credenciales incorrectas' });
        const user = rows[0];
        const match = await bcrypt.compare(password, user.password);
        writeLog(`login2 compare result: ${match}`);
        if (!match) return res.json({ success: false, message: 'Credenciales incorrectas' });
        const token = jwt.sign({ id: user.id, email: user.email }, process.env.JWT_SECRET || 'secreto', { expiresIn: '1h' });
        writeLog('login2 success');
        return res.json({ success: true, message: 'Autenticación exitosa', token, user: { id: user.id, nombre_completo: user.nombre_completo, email: user.email } });
    } catch (e) {
        writeLog(`login2 error: ${e.toString()}`);
        return res.status(500).json({ success: false, message: 'Error en el servidor', error: e.toString() });
    }
});

// Endpoint para verificar token (GET /api/auth/verify)
router.get('/verify', authMiddleware, (req, res) => {
    // authMiddleware agrega req.user con los datos decodificados
    res.json({ success: true, message: 'Token válido', user: { id: req.user.id, email: req.user.email } });
});

module.exports = router;