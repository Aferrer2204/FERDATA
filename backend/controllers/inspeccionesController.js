const db = require('../config/database');

const create = async (req, res) => {
    try {
        const { empresa_id, herramienta_id, fecha, resultado, inspector } = req.body;
        if (!empresa_id || !herramienta_id || !fecha) return res.status(400).json({ message: 'Datos incompletos. Se requiere empresa, herramienta y fecha.' });
        const [result] = await db.promise().execute(
            'INSERT INTO inspecciones (empresa_id, herramienta_id, fecha, resultado, inspector) VALUES (?, ?, ?, ?, ?)',
            [empresa_id, herramienta_id, fecha, resultado || null, inspector || null]
        );

        // Actualizar última inspección en herramientas (campo puede ser ultima_inspeccion o ultimaInspeccion)
        try {
            await db.promise().execute('UPDATE herramientas SET ultima_inspeccion = ? WHERE id = ?', [fecha, herramienta_id]);
        } catch (e) {
            // intentar con nombre alternativo
            await db.promise().execute('UPDATE herramientas SET ultimaInspeccion = ? WHERE id = ?', [fecha, herramienta_id]).catch(() => { });
        }

        return res.status(201).json({ message: 'Inspección creada correctamente.', id: result.insertId });
    } catch (err) {
        console.error('inspecciones.create error', err);
        return res.status(500).json({ message: 'No se pudo crear la inspección.' });
    }
};

const read = async (req, res) => {
    try {
        const [rows] = await db.promise().execute(
            `SELECT i.*, e.nombre as empresa_nombre, h.nombre as herramienta_nombre FROM inspecciones i LEFT JOIN empresas e ON i.empresa_id = e.id LEFT JOIN herramientas h ON i.herramienta_id = h.id ORDER BY i.fecha DESC`
        );
        return res.json(rows);
    } catch (err) {
        console.error('inspecciones.read error', err);
        return res.status(500).json({ message: 'Error al leer inspecciones.' });
    }
};

const get = async (req, res) => {
    try {
        const { id } = req.params;
        // Return enriched record with empresa and herramienta names so the frontend can display details
        const [rows] = await db.promise().execute(
            `SELECT i.*, e.nombre AS empresa_nombre, h.nombre AS herramienta_nombre
             FROM inspecciones i
             LEFT JOIN empresas e ON i.empresa_id = e.id
             LEFT JOIN herramientas h ON i.herramienta_id = h.id
             WHERE i.id = ?`,
            [id]
        );
        if (!rows.length) return res.status(404).json({ message: 'Inspección no encontrada' });
        return res.json(rows[0]);
    } catch (err) {
        console.error('inspecciones.get error', err);
        return res.status(500).json({ message: 'Error al obtener inspección.' });
    }
};

const update = async (req, res) => {
    try {
        const { id } = req.params;
        const { empresa_id, herramienta_id, fecha, resultado, inspector, observaciones } = req.body;
        const [result] = await db.promise().execute(
            'UPDATE inspecciones SET empresa_id = ?, herramienta_id = ?, fecha = ?, resultado = ?, inspector = ?, observaciones = ? WHERE id = ?',
            [empresa_id, herramienta_id, fecha, resultado, inspector, observaciones, id]
        );
        if (result.affectedRows === 0) return res.status(404).json({ message: 'Inspección no encontrada' });
        return res.json({ message: 'Inspección actualizada correctamente.' });
    } catch (err) {
        console.error('inspecciones.update error', err);
        return res.status(500).json({ message: 'Error al actualizar inspección.' });
    }
};

const remove = async (req, res) => {
    try {
        const { id } = req.params;
        const [result] = await db.promise().execute('DELETE FROM inspecciones WHERE id = ?', [id]);
        if (result.affectedRows === 0) return res.status(404).json({ message: 'Inspección no encontrada' });
        return res.json({ message: 'Inspección eliminada correctamente.' });
    } catch (err) {
        console.error('inspecciones.delete error', err);
        return res.status(500).json({ message: 'Error al eliminar inspección.' });
    }
};

module.exports = { create, read, get, update, remove };
