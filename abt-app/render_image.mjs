import puppeteer from 'puppeteer';
import fs from 'fs';

const inputHtmlPath = process.argv[2];
const outputPath = process.argv[3];

if (!inputHtmlPath || !outputPath) {
    console.error('Usage: node render_image.mjs <inputHtmlPath> <outputPath>');
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
        await page.setViewport({ width: 900, height: 1600, deviceScaleFactor: 2.5 });

        const htmlContent = fs.readFileSync(inputHtmlPath, 'utf8');
        await page.setContent(htmlContent, { waitUntil: 'domcontentloaded', timeout: 10000 });

        // Wait a small moment for Tailwind and fonts to render
        await new Promise(r => setTimeout(r, 600));

        const invoiceEl = await page.$('#invoice-document');
        if (invoiceEl) {
            await invoiceEl.screenshot({
                path: outputPath,
                type: 'png',
                omitBackground: false
            });
            console.log('OK');
        } else {
            console.error('Invoice element not found');
            process.exit(1);
        }

        await browser.close();
    } catch (e) {
        console.error('ERROR:', e.message);
        process.exit(1);
    }
})();
