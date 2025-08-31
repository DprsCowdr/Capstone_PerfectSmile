const puppeteer = require('puppeteer');

(async () => {
  // Update baseUrl if your dev server runs on a different host/port
  const baseUrl = process.env.BASE_URL || 'http://localhost:8080';
  const staffUrl = baseUrl + '/staff/appointments';

  const browser = await puppeteer.launch({ args: ['--no-sandbox'], headless: true });
  const page = await browser.newPage();

  // If your app requires auth in the UI, set cookies or localStorage here.
  // For a quick smoke test, the staff dashboard should be reachable in dev with a seeded session.

  await page.goto(staffUrl, { waitUntil: 'networkidle2', timeout: 30000 });

  // Wait for calendar container to load
  await page.waitForSelector('#calendar', { timeout: 10000 }).catch(() => {});

  // Search for any text that looks like a time range, e.g., "09:00–09:45" or "09:00 - 09:45"
  const content = await page.content();

  const timeRangeRegex = /\b\d{2}:\d{2}\s*[–\-]\s*\d{2}:\d{2}\b/;
  const found = timeRangeRegex.test(content);

  if (!found) {
    console.error('No start–end time label found in page.');
    await browser.close();
    process.exit(2);
  }

  console.log('Found start–end time label on staff appointments page.');
  await browser.close();
  process.exit(0);
})();
