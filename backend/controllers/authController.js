const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const User = require('../models/User');

const authController = {
    register: (req, res) => {
        const { nombre, email, password } = req.body;

        // Verificar si el usuario ya existe
        User.findByEmail(email, (err, results) => {
            if (err) {
                return res.status(500).json({ success: false, message: 'Error del servidor' });
            }

            if (results.length > 0) {
                return res.status(400).json({ success: false, message: 'El usuario ya existe' });
            }

            // Hash de la contraseña
            bcrypt.hash(password, 10, (err, hashedPassword) => {
                if (err) {
                    return res.status(500).json({ success: false, message: 'Error al encriptar la contraseña' });
                }

                // Crear usuario
                User.create({ nombre, email, password: hashedPassword }, (error, results) => {
                    if (error) {
                        return res.status(500).json({ success: false, message: 'Error al crear usuario' });
                    }

                    res.status(201).json({
                        success: true,
                        message: 'Usuario creado exitosamente',
                        user: { id: results.insertId, nombre, email }
                    });
                });
            });
        });
    },

    login: (req, res) => {
        const { email, password } = req.body;

        User.findByEmail(email, (err, results) => {
            if (err) {
                return res.status(500).json({ success: false, message: 'Error del servidor' });
            }

            if (results.length === 0) {
                return res.status(401).json({ success: false, message: 'Credenciales incorrectas' });
            }

            const user = results[0];

            // Verificar contraseña
            bcrypt.compare(password, user.password, (error, isMatch) => {
                if (error) {
                    return res.status(500).json({ success: false, message: 'Error del servidor' });
                }

                if (!isMatch) {
                    return res.status(401).json({ success: false, message: 'Credenciales incorrectas' });
                }

                // Generar token
                const token = jwt.sign(
                    { id: user.id, email: user.email },
                    process.env.JWT_SECRET || 'secreto',
                    { expiresIn: '1h' }
                );

                res.json({
                    success: true,
                    message: 'Autenticación exitosa',
                    token,
                    user: { id: user.id, nombre: user.nombre, email: user.email }
                });
            });
        });
    }
};

module.exports = authController;