const db = require('./config/database');
const bcrypt = require('bcryptjs');

const email = process.argv[2] || 'test@example.com';
const plaintext = process.argv[3] || 'test';

async function setPassword() {
  try {
    const hash = await bcrypt.hash(plaintext, 10);
    db.query('UPDATE usuarios SET password = ? WHERE email = ?', [hash, email], (err, result) => {
      if (err) {
        console.error('Error updating password:', err);
        process.exit(1);
      }
      console.log(`Updated password for ${email}, affectedRows=${result.affectedRows}`);
      console.log('New hash:', hash);
      process.exit(0);
    });
  } catch (e) {
    console.error('Hashing error:', e);
    process.exit(1);
  }
}

setPassword();
