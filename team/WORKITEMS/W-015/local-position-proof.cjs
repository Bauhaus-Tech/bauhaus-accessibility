const assert = require('node:assert/strict');
const { chromium } = require('playwright');

// The local site address stays outside the repository so this check can run
// against a developer's own WordPress installation without embedding it here.
const origin = process.env.BAUHAUS_LOCAL_SITE_ORIGIN;
const ip = process.env.BAUHAUS_LOCAL_SITE_IP;

if (!origin || !ip) {
	throw new Error('Set BAUHAUS_LOCAL_SITE_ORIGIN and BAUHAUS_LOCAL_SITE_IP before running this local proof.');
}

async function verifySiennaPositionOverrides() {
	const host = new URL(origin).hostname;
	const browser = await chromium.launch({
		headless: true,
		args: [`--host-resolver-rules=MAP ${host} ${ip}`],
	});

	try {
		const page = await browser.newPage({ viewport: { width: 1280, height: 720 } });
		await page.goto(origin, { waitUntil: 'domcontentloaded' });

		const result = await page.evaluate(() => ({
			generator: document.querySelector('meta[name=generator]')?.getAttribute('content') || null,
			placements: ['left', 'right'].map((side) => {
				const button = document.querySelector('.asw-menu-btn');
				const host = document.body;

				host.classList.remove('bauhaus-widgets-left', 'bauhaus-widgets-right');
				host.classList.add(`bauhaus-widgets-${side}`);
				button.style.left = side === 'left' ? '10px' : 'auto';
				button.style.right = side === 'right' ? '10px' : 'auto';

				const style = getComputedStyle(button);
				return { side, computedLeft: style.left, computedRight: style.right };
			}),
		}));

		const left = result.placements.find((placement) => placement.side === 'left');
		const right = result.placements.find((placement) => placement.side === 'right');

		assert.equal(left.computedLeft, '20px', 'Left mode must override Sienna\'s inline offset.');
		assert.equal(right.computedRight, '20px', 'Right mode must override Sienna\'s inline offset.');
		console.log(JSON.stringify(result));
	} finally {
		await browser.close();
	}
}

verifySiennaPositionOverrides().catch((error) => {
	console.error(error);
	process.exit(1);
});
