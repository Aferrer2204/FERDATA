const db = require('../db');
(async () => {
  try {
    const [rows] = await db.promise().execute('SHOW COLUMNS FROM actividades');
    console.log('columns:', rows);
    process.exit(0);
  } catch (err) {
    console.error(err.stack || err);
    process.exit(1);
  }
})();
