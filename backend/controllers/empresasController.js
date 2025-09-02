const db = require('../db');
const fs = require('fs');
const path = require('path');
const logFile = path.join(__dirname, '..', 'logs', 'server.log');

function appendLogLine(line) {
	try { fs.appendFileSync(logFile, `${new Date().toISOString()} ${line}\n`); } catch (e) { console.error('Failed to append log', e); }
}

const create = async (req, res) => {
	try {
		const { nombre, locacion, taladro, ubicacion, oit, activo } = req.body;
		if (!nombre) return res.status(400).json({ message: 'Datos incompletos. Se requiere al menos un nombre.' });
		// Ensure new companies are active by default unless explicitly set
		const activoVal = typeof activo !== 'undefined' ? (activo ? 1 : 0) : 1;
		const [result] = await db.promise().execute(
			'INSERT INTO empresas (nombre, locacion, taladro, ubicacion, oit, activo) VALUES (?, ?, ?, ?, ?, ?)',
			[nombre, locacion || null, taladro || null, ubicacion || null, oit || null, activoVal]
		);

		// After creating the empresa, add an initial daily actividad for today
		try {
			const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
			const [actRes] = await db.promise().execute(
				'INSERT INTO actividades (fecha, cliente_id, observaciones) VALUES (?, ?, ?)',
				[today, result.insertId, 'Registro inicial']
			);
			appendLogLine(`empresas.create: actividad inicial creada id=${actRes.insertId} for empresa id=${result.insertId}`);
		} catch (actErr) {
			// Log but don't fail the empresa creation if actividad insert fails
			appendLogLine(`empresas.create actividad insert error for empresa id=${result.insertId}: ${actErr && actErr.message ? actErr.message : actErr}`);
			console.error('empresas.create actividad insert error', actErr);
		}

		return res.status(201).json({ message: 'Empresa creada correctamente.', id: result.insertId });
	} catch (err) {
		console.error('empresas.create error', err);
		return res.status(500).json({ message: 'No se pudo crear la empresa.' });
	}
};

const read = async (req, res) => {
	try {
		const [rows] = await db.promise().execute('SELECT * FROM empresas');
		return res.json(rows);
	} catch (err) {
		console.error('empresas.read error', err);
		return res.status(500).json({ message: 'Error al leer empresas.' });
	}
};

const get = async (req, res) => {
	try {
		const { id } = req.params;
		const [rows] = await db.promise().execute('SELECT * FROM empresas WHERE id = ?', [id]);
		if (!rows.length) return res.status(404).json({ message: 'Empresa no encontrada' });
		return res.json(rows[0]);
	} catch (err) {
		console.error('empresas.get error', err);
		return res.status(500).json({ message: 'Error al obtener empresa.' });
	}
};

const update = async (req, res) => {
	try {
		const { id } = req.params;
		const { nombre, locacion, taladro, ubicacion, oit, activo } = req.body;
		const activoVal = typeof activo !== 'undefined' ? (activo ? 1 : 0) : null;
		// Build parameters for update; include activo if provided
		let query = 'UPDATE empresas SET nombre = ?, locacion = ?, taladro = ?, ubicacion = ?, oit = ?';
		const params = [nombre, locacion, taladro, ubicacion, oit];
		if (activoVal !== null) {
			query += ', activo = ?';
			params.push(activoVal);
		}
		query += ' WHERE id = ?';
		params.push(id);
		const [result] = await db.promise().execute(query, params);
		if (result.affectedRows === 0) return res.status(404).json({ message: 'Empresa no encontrada' });
		return res.json({ message: 'Empresa actualizada correctamente.' });
	} catch (err) {
		console.error('empresas.update error', err);
		return res.status(500).json({ message: 'Error al actualizar empresa.' });
	}
};

const remove = async (req, res) => {
	try {
		const { id } = req.params;
		const [result] = await db.promise().execute('DELETE FROM empresas WHERE id = ?', [id]);
		if (result.affectedRows === 0) return res.status(404).json({ message: 'Empresa no encontrada' });
		return res.json({ message: 'Empresa eliminada correctamente.' });
	} catch (err) {
		console.error('empresas.delete error', err);
		return res.status(500).json({ message: 'Error al eliminar empresa.' });
	}
};

module.exports = { create, read, get, update, remove };
