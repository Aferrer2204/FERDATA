const db = require('../backend/db');
const fs = require('fs');

(async () => {
    try {
        const [rows] = await db.promise().execute('SELECT id, nombre, nombre_completo, email, rol, activo FROM usuarios');
        fs.writeFileSync('c:/xampp/xampp/FERDATA/deliverables/data_usuarios.json', JSON.stringify(rows, null, 2), 'utf8');
        console.log('OK');
        process.exit(0);
    } catch (err) {
        console.error('ERR', err.message || err);
        process.exit(2);
    }
})();
