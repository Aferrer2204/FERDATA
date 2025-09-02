const db = require('../config/database');
const bcrypt = require('bcryptjs');

const create = async (req, res) => {
    try {
        const { nombre, email, password, rol, activo } = req.body;
        if (!nombre || !email || !password) return res.status(400).json({ message: 'Datos incompletos. Se requiere nombre, email y password.' });
        const password_hash = await bcrypt.hash(password, 10);
        const [result] = await db.promise().execute(
            'INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, ?, ?)',
            [nombre, email, password_hash, rol || 'user', activo || 1]
        );
        return res.status(201).json({ message: 'Usuario creado correctamente.', id: result.insertId });
    } catch (err) {
        console.error('usuarios.create error', err);
        return res.status(500).json({ message: 'No se pudo crear el usuario.' });
    }
};

const read = async (req, res) => {
    try {
        const [rows] = await db.promise().execute('SELECT id, nombre, email, rol, activo FROM usuarios');
        return res.json(rows);
    } catch (err) {
        console.error('usuarios.read error', err);
        return res.status(500).json({ message: 'Error al leer usuarios.' });
    }
};

const get = async (req, res) => {
    try {
        const { id } = req.params;
        const [rows] = await db.promise().execute('SELECT id, nombre, email, rol, activo FROM usuarios WHERE id = ?', [id]);
        if (!rows.length) return res.status(404).json({ message: 'Usuario no encontrado' });
        return res.json(rows[0]);
    } catch (err) {
        console.error('usuarios.get error', err);
        return res.status(500).json({ message: 'Error al obtener usuario.' });
    }
};

const update = async (req, res) => {
    try {
        const { id } = req.params;
        const { nombre, email, password, rol, activo } = req.body;
        let password_hash = null;
        if (password) password_hash = await bcrypt.hash(password, 10);
        const [result] = await db.promise().execute(
            'UPDATE usuarios SET nombre = ?, email = ?, password = COALESCE(?, password), rol = ?, activo = ? WHERE id = ?',
            [nombre, email, password_hash, rol, activo, id]
        );
        if (result.affectedRows === 0) return res.status(404).json({ message: 'Usuario no encontrado' });
        return res.json({ message: 'Usuario actualizado correctamente.' });
    } catch (err) {
        console.error('usuarios.update error', err);
        return res.status(500).json({ message: 'Error al actualizar usuario.' });
    }
};

const remove = async (req, res) => {
    try {
        const { id } = req.params;
        const [result] = await db.promise().execute('DELETE FROM usuarios WHERE id = ?', [id]);
        if (result.affectedRows === 0) return res.status(404).json({ message: 'Usuario no encontrado' });
        return res.json({ message: 'Usuario eliminado correctamente.' });
    } catch (err) {
        console.error('usuarios.delete error', err);
        return res.status(500).json({ message: 'Error al eliminar usuario.' });
    }
};

module.exports = { create, read, get, update, remove };
