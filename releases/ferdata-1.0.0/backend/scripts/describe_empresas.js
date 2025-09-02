const db = require('../config/database');

async function describe() {
    try {
        const [rows] = await db.promise().query('SHOW COLUMNS FROM empresas');
        console.log(rows);
        process.exit(0);
    } catch (e) {
        console.error('Error', e);
        process.exit(1);
    }
}

describe();
