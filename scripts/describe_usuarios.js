const db = require('../backend/db');
(async () => {
    try {
        const [rows] = await db.promise().execute("SHOW COLUMNS FROM usuarios");
        console.log(JSON.stringify(rows, null, 2));
        process.exit(0);
    } catch (err) {
        console.error('ERR', err.message || err);
        process.exit(2);
    }
})();
