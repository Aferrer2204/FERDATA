const db = require('../db');
const fs = require('fs');
const path = require('path');
const logFile = path.join(__dirname, '..', 'logs', 'server.log');
function writeLogLine(line) {
	try { fs.appendFileSync(logFile, `${new Date().toISOString()} ${line}\n`); } catch (e) { console.error('Failed to write controller log', e); }
}

async function create(req, res) {
	try {
		// The actividades table columns (current DB) are: fecha, empresa, contacto, avance, locacion, usuario, observaciones
		// Accept alternate/legacy field names from frontend for backward compatibility
		const fecha = req.body.fecha || new Date().toISOString().split('T')[0];
		const empresa = req.body.empresa || req.body.cliente_id || req.body.empresa_id || null;
		const contacto = req.body.contacto || null;
		const locacion = req.body.locacion || req.body.lugar || null;
		const usuario = req.body.usuario || req.body.inspector || req.body.tipo || null;
		const observaciones = req.body.observaciones || req.body.descripcion || null;
		const avance = req.body.avance || null;

		const [result] = await db.promise().execute(
			'INSERT INTO actividades (fecha, empresa, contacto, avance, locacion, usuario, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)',
			[fecha, empresa, contacto, avance, locacion, usuario, observaciones]
		);
		return res.status(201).json({ message: 'Actividad creada', id: result.insertId });
	} catch (err) {
		console.error('actividades.create error', err);
		writeLogLine(`actividades.create error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al crear actividad' });
	}
}

// Generate a daily actividad for each active empresa (idempotent for a given date)
async function generateDaily(req, res) {
	try {
		const today = new Date().toISOString().split('T')[0];
		// find active empresas (activo = 1)
		const [empresas] = await db.promise().execute('SELECT id FROM empresas WHERE activo = 1');
		let inserted = 0;
		for (const e of empresas) {
			// Check if an actividad for today already exists for this empresa
			const [exists] = await db.promise().execute('SELECT id FROM actividades WHERE empresa = ? AND fecha = ?', [e.id, today]);
			if (!exists || exists.length === 0) {
				const [r] = await db.promise().execute('INSERT INTO actividades (fecha, empresa, observaciones) VALUES (?, ?, ?)', [today, e.id, 'Actividad diaria automática']);
				inserted++;
				writeLogLine(`actividades.generateDaily: inserted id=${r.insertId} for empresa=${e.id}`);
			}
		}
		return res.json({ message: 'Generación diaria completada', inserted });
	} catch (err) {
		console.error('actividades.generateDaily error', err);
		writeLogLine(`actividades.generateDaily error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al generar actividades diarias' });
	}
}

async function read(req, res) {
	try {
		const [rows] = await db.promise().execute(
			`SELECT a.*, e.nombre as cliente_nombre FROM actividades a LEFT JOIN empresas e ON a.empresa = e.id ORDER BY fecha DESC`
		);
		return res.json(rows);
	} catch (err) {
		console.error('actividades.read error', err);
		writeLogLine(`actividades.read error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al leer actividades.' });
	}
}

async function get(req, res) {
	try {
		const id = req.params.id;
		const [rows] = await db.promise().execute('SELECT * FROM actividades WHERE id = ?', [id]);
		if (!rows || rows.length === 0) return res.status(404).json({ message: 'Actividad no encontrada' });
		return res.json(rows[0]);
	} catch (err) {
		console.error('actividades.get error', err);
		writeLogLine(`actividades.get error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al obtener actividad' });
	}
}

async function update(req, res) {
	try {
		const id = req.params.id;
		const fecha = req.body.fecha || null;
		const empresa = req.body.empresa || req.body.cliente_id || req.body.empresa_id || null;
		const contacto = req.body.contacto || null;
		const locacion = req.body.locacion || req.body.lugar || null;
		const usuario = req.body.usuario || req.body.inspector || req.body.tipo || null;
		const observaciones = req.body.observaciones || req.body.descripcion || null;
		const avance = req.body.avance || null;

		const [result] = await db.promise().execute(
			'UPDATE actividades SET fecha = ?, empresa = ?, contacto = ?, avance = ?, locacion = ?, usuario = ?, observaciones = ? WHERE id = ?',
			[fecha, empresa, contacto, avance, locacion, usuario, observaciones, id]
		);
		return res.json({ message: 'Actividad actualizada', affectedRows: result.affectedRows });
	} catch (err) {
		console.error('actividades.update error', err);
		writeLogLine(`actividades.update error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al actualizar actividad' });
	}
}

async function _delete(req, res) {
	try {
		const id = req.params.id;
		const [result] = await db.promise().execute('DELETE FROM actividades WHERE id = ?', [id]);
		return res.json({ message: 'Actividad eliminada', affectedRows: result.affectedRows });
	} catch (err) {
		console.error('actividades.delete error', err);
		writeLogLine(`actividades.delete error ${err && err.stack ? err.stack : err}`);
		return res.status(500).json({ message: 'Error al eliminar actividad' });
	}
}

module.exports = {
	create,
	read,
	get,
	update,
	delete: _delete,
	generateDaily
};


