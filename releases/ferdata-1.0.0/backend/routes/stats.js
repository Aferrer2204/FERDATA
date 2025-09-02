const express = require('express');
const router = express.Router();
const db = require('../db');

router.get('/', async (req, res) => {
    try {
        // fetch counts in parallel; include both actividades and inspecciones tables if present
        const queries = [
            db.promise().execute('SELECT COUNT(*) AS cnt FROM empresas'),
            db.promise().execute('SELECT COUNT(*) AS cnt FROM herramientas'),
            db.promise().execute('SELECT COUNT(*) AS cnt FROM inspecciones'),
            db.promise().execute('SELECT COUNT(*) AS cnt FROM reportes'),
            // keep actividades for backward compatibility if some parts of frontend expect it
            db.promise().execute('SELECT COUNT(*) AS cnt FROM actividades')
        ];

        const [[emp], [her], [ins], [rep], [act]] = await Promise.all(queries);

        // also ask the server which database it's connected to (helps detect env mismatch)
        let dbName = null;
        try {
            const [dbRes] = await db.promise().execute('SELECT DATABASE() AS db');
            if (dbRes && dbRes[0] && dbRes[0].db) dbName = dbRes[0].db;
        } catch (e) {
            // ignore
        }

        return res.json({
            empresas: emp[0].cnt || 0,
            herramientas: her[0].cnt || 0,
            inspecciones: ins[0].cnt || 0,
            reportes: rep[0].cnt || 0,
            actividades: act[0].cnt || 0,
            dbName
        });
    } catch (err) {
        console.error('stats error', err);
        // If one of the tables doesn't exist or query fails, attempt a best-effort fallback
        try {
            const [empRow] = await db.promise().execute('SELECT COUNT(*) AS cnt FROM empresas');
            const [herRow] = await db.promise().execute('SELECT COUNT(*) AS cnt FROM herramientas');
            const empresas = (empRow && empRow[0] && empRow[0].cnt) || 0;
            const herramientas = (herRow && herRow[0] && herRow[0].cnt) || 0;
            return res.json({ empresas, herramientas, inspecciones: 0, reportes: 0, actividades: 0 });
        } catch (err2) {
            console.error('stats fallback error', err2);
            return res.status(500).json({ message: 'Error al obtener estadísticas' });
        }
    }
});

module.exports = router;