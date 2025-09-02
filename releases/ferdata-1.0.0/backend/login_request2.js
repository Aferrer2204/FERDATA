const http = require('http');

const data = JSON.stringify({ email: 'test@example.com', password: 'test' });

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/api/auth/login2',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(data)
  }
};

const req = http.request(options, (res) => {
  let body = '';
  res.on('data', chunk => body += chunk);
  res.on('end', () => {
    console.log('Status:', res.statusCode);
    try {
      console.log('Body:', JSON.parse(body));
    } catch (e) {
      console.log('Body (raw):', body);
    }
    process.exit(0);
  });
});

req.on('error', (e) => {
  console.error('Request error:', e);
  process.exit(1);
});

req.setTimeout(5000, () => {
  console.error('Request timed out');
  req.abort();
});

try {
  req.write(data);
  req.end();
} catch (e) {
  console.error('Request send error:', e);
  process.exit(1);
}
