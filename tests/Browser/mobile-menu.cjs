const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium } = require('playwright');

test('mobile menu hides its entire contents immediately, including transitioned links', async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        for (const width of [320, 375, 430]) {
            const page = await browser.newPage({ viewport: { width, height: 900 }, hasTouch: true });
            await page.setContent(`<!doctype html><html><body>
              <header class="av-nav" id="avNav"><div class="av-nav__inner"><span>Logo</span><button class="av-nav__hamburger" id="avNavHamburger" aria-expanded="false">Menu</button></div>
              <div class="av-drawer" id="avDrawer" aria-hidden="true" hidden inert><nav class="av-drawer__panel"><ul class="av-drawer__list">
              <li><a class="av-drawer__link" href="#tour">Kilimanjaro</a></li><li><a class="av-drawer__link" href="#tour">Safari</a></li><li><a class="av-drawer__link" href="#tour">Day Trips</a></li></ul>
              <a class="av-drawer__whatsapp" href="#tour">WhatsApp</a></nav></div></header>
              <main id="tour" style="height:1800px">Breadcrumb and tour content</main></body></html>`);
            await page.addStyleTag({ path: 'front-end/html/assets/css/style.css' });
            // Reproduce descendants with their own visibility and delayed transitions.
            await page.addStyleTag({ content: '.av-drawer a { visibility: visible !important; transition: all .5s; }' });
            const source = fs.readFileSync('resources/views/frontend/partials/header.blade.php', 'utf8');
            await page.addScriptTag({ content: source.split('<script data-cfasync="false">')[1].split('</script>')[0] });
            await page.evaluate(() => document.dispatchEvent(new Event('DOMContentLoaded')));
            const assertClosed = async () => {
                assert.equal(await page.locator('#avDrawer').evaluate(el => el.hidden && el.inert && getComputedStyle(el).display === 'none'), true);
                assert.equal(await page.locator('#avDrawer a').evaluateAll(links => links.every(link => link.getClientRects().length === 0)), true);
                assert.equal(await page.locator('#avNavHamburger').getAttribute('aria-expanded'), 'false');
                assert.equal(await page.locator('body').evaluate(el => el.classList.contains('av-no-scroll')), false);
            };
            await assertClosed();
            for (let index = 0; index < 3; index++) {
                await page.locator('#avNavHamburger').tap();
                assert.equal(await page.locator('#avDrawer a').first().isVisible(), true);
                await page.locator('#avNavHamburger').tap();
                await assertClosed();
            }
            await page.locator('#avNavHamburger').tap();
            await page.keyboard.press('Escape');
            await assertClosed();
            await page.locator('#avNavHamburger').tap();
            await page.setViewportSize({ width: 1280, height: 900 });
            await page.waitForFunction(() => document.getElementById('avDrawer').hidden);
            await assertClosed();
            await page.close();
        }
    } finally { await browser.close(); }
});
