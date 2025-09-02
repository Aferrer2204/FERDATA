const db = require('./config/database');

console.log('Starting DB test...');

db.query('SELECT 1 + 1 AS sum', (err, results) => {
  if (err) {
    console.error('DB test error:', err);
    process.exit(1);
  }
  console.log('DB test result:', results);
  process.exit(0);
});
