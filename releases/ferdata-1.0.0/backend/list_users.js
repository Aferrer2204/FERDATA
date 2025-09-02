const db = require('./config/database');

console.log('Listing users...');

db.query('SELECT id, nombre_completo, email FROM usuarios LIMIT 100', (err, results) => {
  if (err) {
    console.error('Error querying usuarios:', err);
    process.exit(1);
  }
  console.log('users:', results);
  process.exit(0);
});
