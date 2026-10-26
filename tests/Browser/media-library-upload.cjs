const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

test('50 uploads use separate requests; failed images remain for retry in the chosen subcategory', async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const page = await browser.newPage();
        const attempts = [];
        await page.route('http://media-library.test/**', async route => {
            if (route.request().method() === 'GET') {
                await route.fulfill({ contentType: 'text/html', body: `<!doctype html><html><body>
                  <div id="uploadModal"><button data-bs-dismiss="modal">Close</button>
                  <form id="media-upload-form" action="/upload"><input name="_token" value="test">
                  <div id="upload-dropzone"><input id="upload-file-input" type="file" multiple></div>
                  <div id="upload-preview"></div><p id="upload-selection-count"></p>
                  <div id="upload-progress-wrap"><div id="upload-progress-bar"></div><p id="upload-progress-text"></p></div>
                  <div id="upload-results"></div><input id="upload-category-search">
                  <select id="upload-category"><option value="">No category</option>
                  <option value="2">Accommodations &gt; River Camp</option>
                  <option value="5">National Park &gt; Mount Meru &gt; Summit &gt; Deep camp</option>
                  <option value="6">Kilimanjaro National Park</option>
                  <option value="7">Kilimanjaro National Park &gt; Mount Kilimanjaro &gt; Summit Camp</option></select>
                  <p id="upload-category-empty" hidden>No categories match.</p>
                  <button id="upload-submit-btn"><span id="upload-submit-spinner"></span><span id="upload-submit-label"></span></button>
                  </form></div></body></html>` });
                return;
            }
            const body = route.request().postDataBuffer().toString();
            const name = body.match(/filename="([^"]+)"/)[1];
            assert.equal((body.match(/filename=/g) || []).length, 1, 'each request contains one file');
            assert.match(body, /name="category_id"\r\n\r\n5\r\n/, 'all requests retain the selected subcategory');
            attempts.push(name);
            await route.fulfill({
                status: name === 'image-21.png' && attempts.filter(item => item === name).length === 1 ? 422 : 200,
                contentType: 'application/json',
                body: JSON.stringify(name === 'image-21.png' && attempts.filter(item => item === name).length === 1
                    ? { errors: { 'files.0': ['Test rejection'] } }
                    : { uploaded: [{ id: attempts.length, name }] }),
            });
        });
        await page.goto('http://media-library.test/');
        await page.addScriptTag({ path: path.resolve('resources/views/admin/media/partials/upload-script.blade.php') });
        // Loading the script after DOMContentLoaded must initialize immediately.
        await page.locator('#upload-category-search').fill('kilimanjaro');
        assert.deepEqual(await page.locator('#upload-category option').allTextContents(), [
            'No category', 'Kilimanjaro National Park', 'Kilimanjaro National Park > Mount Kilimanjaro > Summit Camp',
        ]);
        await page.locator('#upload-category-search').fill('missing-category');
        assert.equal(await page.locator('#upload-category-empty').isVisible(), true);
        await page.locator('#upload-category-search').fill('');
        assert.equal(await page.locator('#upload-category option').count(), 5);
        // Repeated ready events must not register duplicate upload handlers.
        await page.evaluate(() => document.dispatchEvent(new Event('DOMContentLoaded')));
        await page.locator('#upload-category-search').fill('deep camp');
        assert.equal(await page.locator('#upload-category option').count(), 2);
        await page.locator('#upload-category').selectOption('5');
        const files = Array.from({ length: 50 }, (_, index) => ({ name: `image-${index + 1}.png`, mimeType: 'image/png', buffer: Buffer.from('test-image') }));
        await page.locator('#upload-file-input').setInputFiles(files);
        assert.match(await page.locator('#upload-selection-count').textContent(), /^50 images selected/);
        assert.equal(await page.locator('#upload-preview img').count(), 12);
        await page.locator('#upload-submit-btn').click();
        await page.waitForFunction(() => document.querySelector('#upload-progress-text').textContent === '49 of 50 images uploaded. 1 failed.');
        assert.equal(attempts.length, 50);
        assert.equal(new Set(attempts).size, 50);
        assert.equal(await page.locator('#upload-submit-label').textContent(), 'Retry remaining files');
        assert.match(await page.locator('#upload-selection-count').textContent(), /^1 images selected/);
        assert.match(await page.locator('#upload-results').textContent(), /image-21.png: Test rejection/);
        assert.match(await page.locator('#upload-results a').getAttribute('href'), /category_id=5/);
        await page.locator('#upload-submit-btn').click();
        await page.waitForFunction(() => document.querySelector('#upload-progress-text').textContent === '1 of 1 images uploaded. 0 failed.');
        assert.equal(attempts.length, 51);
        assert.equal(attempts.at(-1), 'image-21.png');
        assert.equal(await page.locator('#upload-submit-btn').isDisabled(), true);
    } finally {
        await browser.close();
    }
});

test('category search opens deep matches and restores collapsed folders when cleared', async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const page = await browser.newPage();
        await page.setContent(`<input id="category-search"><p id="category-search-empty" hidden>No categories match.</p>
          <ul id="media-category-tree">
            <li data-category-node data-category-name="National Park"><details><summary>National Park</summary><div><a data-category-link="1">All in National Park</a><ul>
              <li data-category-node data-category-name="Mount Meru"><details><summary>Mount Meru</summary><div><a data-category-link="2">All in Mount Meru</a><ul>
                <li data-category-node data-category-name="Summit camp"><a data-category-link="3">Summit camp</a></li>
              </ul></div></details></li>
            </ul></div></details></li>
            <li data-category-node data-category-name="Accommodations"><details><summary>Accommodations</summary><div><a data-category-link="4">All in Accommodations</a><ul>
              <li data-category-node data-category-name="River camp"><a data-category-link="5">River camp</a></li>
            </ul></div></details></li>
          </ul>`);
        const source = fs.readFileSync('resources/views/admin/media/index.blade.php', 'utf8');
        const script = source.split('<script>')[1].split('</script>')[0].replace(/\{\{[\s\S]*?\}\}/g, '3');
        await page.addScriptTag({ content: script });
        await page.evaluate(() => document.dispatchEvent(new Event('DOMContentLoaded')));
        assert.equal(await page.locator('details[open]').count(), 2, 'active deep category expands its ancestors');
        await page.locator('#category-search').fill('River');
        assert.equal(await page.locator('[data-category-name="National Park"]').isVisible(), false);
        assert.equal(await page.locator('[data-category-name="River camp"]').isVisible(), true);
        await page.locator('#category-search').fill('missing category');
        assert.equal(await page.locator('#category-search-empty').isVisible(), true);
        await page.locator('#category-search').fill('');
        assert.equal(await page.locator('details[open]').count(), 2);
        assert.equal(await page.locator('[data-category-name="Summit camp"]').isVisible(), true);
        await page.locator('#category-search').fill('National Park');
        assert.equal(await page.locator('[data-category-name="Summit camp"]').isVisible(), true, 'matching a parent also reveals descendants');
    } finally {
        await browser.close();
    }
});
