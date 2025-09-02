const db = require('./db');

(async () => {
    try {
        const pool = db.promise();
        const queries = [
            pool.execute('SELECT COUNT(*) AS cnt FROM empresas'),
            pool.execute('SELECT COUNT(*) AS cnt FROM herramientas'),
            pool.execute('SELECT COUNT(*) AS cnt FROM inspecciones'),
            pool.execute('SELECT COUNT(*) AS cnt FROM reportes'),
            pool.execute('SELECT COUNT(*) AS cnt FROM actividades')
        ];

        const results = await Promise.all(queries);
        const empresas = results[0][0][0].cnt || 0;
        const herramientas = results[1][0][0].cnt || 0;
        const inspecciones = results[2][0][0].cnt || 0;
        const reportes = results[3][0][0].cnt || 0;
        const actividades = results[4][0][0].cnt || 0;

        const out = { empresas, herramientas, inspecciones, reportes, actividades };
        console.log(JSON.stringify(out, null, 2));
        process.exit(0);
    } catch (err) {
        console.error('DB check error:', err && err.message ? err.message : err);
        process.exit(2);
    }
})();
