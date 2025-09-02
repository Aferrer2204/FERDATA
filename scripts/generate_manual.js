const fs = require('fs');
const path = require('path');
const md = require('markdown-it')({ html: true });
const args = process.argv.slice(2);
const makePdf = args.includes('--pdf');

const mdPath = path.join(__dirname, '..', 'docs', 'USER_MANUAL.md');
const outHtml = path.join(__dirname, '..', 'docs', 'USER_MANUAL.html');
const mdSrc = fs.readFileSync(mdPath, 'utf8');

const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>FERDATA - Manual de Usuario</title>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; line-height: 1.6; padding: 24px; max-width: 900px; margin: auto; }
h1,h2,h3 { color: #111; }
img { max-width: 100%; border: 1px solid #ddd; padding: 4px; background: #fff; }
pre { background: #f6f8fa; padding: 12px; overflow: auto; }
code { background: #f6f8fa; padding: 2px 4px; }
figure { margin: 0 0 16px 0; }
figcaption { font-size: 0.9em; color: #555; }
</style>
</head>
<body>
${md.render(mdSrc)}
</body>
</html>`;

fs.writeFileSync(outHtml, html, 'utf8');
console.log('Wrote', outHtml);

if (makePdf) {
  (async () => {
    try {
      const puppeteer = require('puppeteer');
      const browser = await puppeteer.launch({ args: ['--no-sandbox','--disable-setuid-sandbox'] });
      const page = await browser.newPage();
      await page.setContent(html, { waitUntil: 'networkidle0' });
      const outPdf = path.join(__dirname, '..', 'docs', 'USER_MANUAL.pdf');
      await page.pdf({ path: outPdf, format: 'A4', printBackground: true });
      await browser.close();
      console.log('Wrote', outPdf);
    } catch (err) {
      console.error('PDF generation failed (puppeteer may not be installed):', err.message);
      process.exitCode = 2;
    }
  })();
}
