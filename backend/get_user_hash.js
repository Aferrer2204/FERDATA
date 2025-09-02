const db = require('./config/database');
const bcrypt = require('bcryptjs');

const email = process.argv[2] || 'test@example.com';

db.query('SELECT id, nombre_completo, email, password FROM usuarios WHERE email = ?', [email], async (err, results) => {
  if (err) {
    console.error('Error querying usuarios:', err);
    process.exit(1);
  }
  if (!results || results.length === 0) {
    console.log('No user found for', email);
    process.exit(0);
  }
  const user = results[0];
  console.log('Found user:', { id: user.id, nombre_completo: user.nombre_completo, email: user.email });
  console.log('Stored password hash:', user.password);

  // quick check: compare with plaintext 'test'
  const plaintext = 'test';
  const match = await bcrypt.compare(plaintext, user.password);
  console.log(`bcrypt.compare('${plaintext}', hash) =>`, match);
  process.exit(0);
});
