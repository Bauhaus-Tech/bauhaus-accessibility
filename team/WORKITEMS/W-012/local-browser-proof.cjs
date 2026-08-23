const assert = require('node:assert/strict');
const { chromium } = require('playwright');

// The site address and IP stay outside the repository because this is a
// developer-only proof against a local WordPress installation.
const origin = process.env.BAUHAUS_LOCAL_SITE_ORIGIN;
const ip = process.env.BAUHAUS_LOCAL_SITE_IP;

if (!origin || !ip) {
  throw new Error('Set BAUHAUS_LOCAL_SITE_ORIGIN and BAUHAUS_LOCAL_SITE_IP before running this local proof.');
}

async function verifyLocalSiennaToolbar() {
  const host = new URL(origin).hostname;
  // The browser needs this mapping because the local site's development host
  // is not necessarily present in the operating system DNS configuration.
  const browser = await chromium.launch({
    headless: true,
    args: [`--host-resolver-rules=MAP ${host} ${ip}`],
  });
  const page = await browser.newPage({ viewport: { width: 1280, height: 720 } });
  const siennaRequests = [];

  // Ignore unrelated page assets; the assertion is specifically about every
  // asset the Sienna integration can load during these toolbar interactions.
  page.on('request', (request) => {
    if (/sienna-accessibility|OpenDyslexic|assets\/locales|cdn\.jsdelivr/i.test(request.url())) {
      siennaRequests.push(request.url());
    }
  });

  try {
    await page.goto(origin, { waitUntil: 'domcontentloaded' });
    const toolbarButton = page.locator('.asw-menu-btn');
    await toolbarButton.focus();
    assert.equal(
      await toolbarButton.evaluate((element) => document.activeElement === element),
      true,
      'The toolbar button should be keyboard-focusable.'
    );
    await page.keyboard.press('Enter');
    assert.equal(await page.locator('.asw-menu').count(), 1, 'The toolbar menu should open.');

    await page.locator('[data-key=high-contrast]').click();
    assert.equal(
      await page.locator('html').evaluate((element) => element.classList.contains('aws-filter')),
      true,
      'High contrast should update the page.'
    );

    const localFont = page.waitForResponse(
      (response) => /assets\/js\/fonts\/OpenDyslexic3-Regular\.woff$/.test(response.url()) && response.ok()
    );
    await page.locator('[data-key=readable-font]').click();
    await localFont;

    assert.equal(
      siennaRequests.every((url) => new URL(url).origin === origin),
      true,
      `Unexpected remote Sienna request: ${siennaRequests.join(', ')}`
    );
    console.log(JSON.stringify({ origin, toolbar: 'opened-by-keyboard', highContrast: true, siennaRequests }, null, 2));
  } finally {
    await browser.close();
  }
}

verifyLocalSiennaToolbar().catch((error) => {
  console.error(error);
  process.exit(1);
});
