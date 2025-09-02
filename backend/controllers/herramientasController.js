const db = require('../config/database');

const create = async (req, res) => {
	try {
		const { nombre, tipo, empresa_id } = req.body;
		if (!nombre || !empresa_id) return res.status(400).json({ message: 'Datos incompletos. Se requiere nombre y empresa_id.' });
		const [result] = await db.promise().execute(
			'INSERT INTO herramientas (nombre, empresa_id, tipo) VALUES (?, ?, ?)',
			[nombre, empresa_id || null, tipo || null]
		);
		return res.status(201).json({ message: 'Herramienta creada correctamente.', id: result.insertId });
	} catch (err) {
		console.error('herramientas.create error', err);
		return res.status(500).json({ message: 'No se pudo crear la herramienta.' });
	}
};

const read = async (req, res) => {
	try {
		const [rows] = await db.promise().execute('SELECT * FROM herramientas');
		return res.json(rows);
	} catch (err) {
		console.error('herramientas.read error', err);
		return res.status(500).json({ message: 'Error al leer herramientas.' });
	}
};

const get = async (req, res) => {
	try {
		const { id } = req.params;
		const [rows] = await db.promise().execute('SELECT * FROM herramientas WHERE id = ?', [id]);
		if (!rows.length) return res.status(404).json({ message: 'Herramienta no encontrada' });
		return res.json(rows[0]);
	} catch (err) {
		console.error('herramientas.get error', err);
		return res.status(500).json({ message: 'Error al obtener herramienta.' });
	}
};

const update = async (req, res) => {
	try {
		const { id } = req.params;
		const { nombre, tipo, serial } = req.body;
		const [result] = await db.promise().execute(
			'UPDATE herramientas SET nombre = ?, tipo = ?, serial = ? WHERE id = ?',
			[nombre, tipo, serial, id]
		);
		if (result.affectedRows === 0) return res.status(404).json({ message: 'Herramienta no encontrada' });
		return res.json({ message: 'Herramienta actualizada correctamente.' });
	} catch (err) {
		console.error('herramientas.update error', err);
		return res.status(500).json({ message: 'Error al actualizar herramienta.' });
	}
};

const remove = async (req, res) => {
	try {
		const { id } = req.params;
		const [result] = await db.promise().execute('DELETE FROM herramientas WHERE id = ?', [id]);
		if (result.affectedRows === 0) return res.status(404).json({ message: 'Herramienta no encontrada' });
		return res.json({ message: 'Herramienta eliminada correctamente.' });
	} catch (err) {
		console.error('herramientas.delete error', err);
		return res.status(500).json({ message: 'Error al eliminar herramienta.' });
	}
};

module.exports = { create, read, get, update, remove };

