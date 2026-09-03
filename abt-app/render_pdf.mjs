import puppeteer from 'puppeteer';
import fs from 'fs';

const inputHtmlPath = process.argv[2];
const outputPath = process.argv[3];

if (!inputHtmlPath || !outputPath) {
    console.error('Usage: node render_pdf.mjs <inputHtmlPath> <outputPath>');
    process.exit(1);
}

(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--allow-file-access-from-files']
        });
        const page = await browser.newPage();
        await page.setViewport({ width: 900, height: 1600, deviceScaleFactor: 2 });

        const htmlContent = fs.readFileSync(inputHtmlPath, 'utf8');
        await page.setContent(htmlContent, { waitUntil: 'domcontentloaded', timeout: 10000 });

        await new Promise(r => setTimeout(r, 600));

        await page.pdf({
            path: outputPath,
            format: 'A4',
            printBackground: true,
            margin: { top: '6mm', right: '6mm', bottom: '6mm', left: '6mm' }
        });

        await browser.close();
        console.log('OK');
    } catch (e) {
        console.error('ERROR:', e.message);
        process.exit(1);
    }
})();
