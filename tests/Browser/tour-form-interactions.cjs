const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

test('new itinerary picker loads selectable photos after initial page load', async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const page = await browser.newPage();
        await page.route('http://picker.test/**', route => route.request().url().includes('/search')
            ? route.fulfill({ contentType: 'application/json', body: JSON.stringify({ images: [5, 3, 8].map(id => ({ id, name: id === 5 ? 'Camp' : 'Photo ' + id, display_title: 'Photo ' + id, thumb_url: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==' })), pagination: { current_page: 1, last_page: 1, total: 1 } }) })
            : route.fulfill({ contentType: 'text/html', body: '<!doctype html><html><body></body></html>' }));
        await page.goto('http://picker.test/');
        const template = fs.readFileSync('resources/views/admin/media/partials/modal-picker.blade.php', 'utf8');
        const script = template.split('<script>')[1].split('</script>')[0].replace(/\{\{[\s\S]*?\}\}/g, '/search');
        await page.addScriptTag({ content: script });
        await page.evaluate(() => document.dispatchEvent(new Event('DOMContentLoaded')));
        await page.evaluate(() => {
            document.body.innerHTML = `<div class="media-picker-modal" id="picker-new-day" data-multiple="1"><input class="picker-search"><select class="picker-category-filter"><option value="">All</option></select><select class="picker-sort"><option>newest</option></select><div class="picker-results"></div><nav class="picker-pagination"></nav><button class="picker-confirm-btn" disabled>Use Selected</button><span class="picker-selected-summary"></span></div>`;
        });
        await page.waitForTimeout(50);
        await page.evaluate(() => document.querySelector('.media-picker-modal').dispatchEvent(new Event('show.bs.modal')));
        await page.locator('.picker-item').first().waitFor();
        await page.locator('.picker-item').first().click();
        assert.equal(await page.locator('.picker-confirm-btn').isDisabled(), false);
        assert.equal(await page.locator('.picker-selected-summary').textContent(), 'Camp');
        await page.locator('.picker-item[data-media-id="8"]').click();
        await page.locator('.picker-item[data-media-id="3"]').click();
        assert.equal(await page.locator('.picker-item[data-media-id="8"] .picker-selected-check').textContent(), '2');
        const component = fs.readFileSync('resources/views/components/media-picker.blade.php', 'utf8');
        await page.addScriptTag({ content: component.split('<script>')[1].split('</script>')[0] });
        await page.evaluate(() => {
            window.bootstrap = { Modal: { getInstance: () => ({ hide() {} }) } };
            const widget = document.createElement('div');
            widget.className = 'media-picker';
            widget.dataset.pickerId = 'new-day';
            widget.dataset.inputName = 'itinerary_days[0][existing_image_ids]';
            widget.dataset.multiple = '1';
            widget.innerHTML = '<div class="media-picker-preview media-picker-sortable"></div>';
            document.body.appendChild(widget);
        });
        await page.locator('.picker-confirm-btn').click();
        assert.deepEqual(await page.locator('.media-picker input[type="hidden"]').evaluateAll(items => items.map(item => item.value)), ['5', '8', '3']);
        assert.deepEqual(await page.locator('.media-picker-order').allTextContents(), ['1', '2', '3']);
        await page.evaluate(() => document.querySelector('.media-picker-modal').dispatchEvent(new Event('show.bs.modal')));
        await page.waitForFunction(() => document.querySelectorAll('.picker-selected-check').length === 3);
        assert.equal(await page.locator('.picker-item[data-media-id="8"] .picker-selected-check').textContent(), '2');
        await page.locator('.picker-item[data-media-id="8"]').click();
        await page.locator('.picker-item[data-media-id="8"]').click();
        await page.locator('.picker-confirm-btn').click();
        assert.deepEqual(await page.locator('.media-picker input[type="hidden"]').evaluateAll(items => items.map(item => item.value)), ['5', '3', '8']);
        await page.locator('.media-picker-thumb[data-id="5"] .media-picker-remove').click();
        assert.deepEqual(await page.locator('.media-picker-order').allTextContents(), ['1', '2']);

    } finally { await browser.close(); }
});

test('TinyMCE supports Backspace and Delete in pasted and previously locked text', async () => {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    try {
        const page = await browser.newPage();
        await page.route('http://editor.test/**', async route => {
            const url = new URL(route.request().url());
            if (!url.pathname.startsWith('/tinymce/')) return route.fulfill({ contentType: 'text/html', body: '<!doctype html><html><body><textarea id="editor"><p contenteditable="false">Editable text</p></textarea></body></html>' });
            const base = path.resolve('asset/js/tinymce');
            const file = path.resolve(base, url.pathname.slice(9));
            if (!file.startsWith(base + path.sep) || !fs.existsSync(file)) return route.fulfill({ status: 404, body: '' });
            return route.fulfill({ path: file, contentType: file.endsWith('.js') ? 'application/javascript' : 'text/css' });
        });
        await page.goto('http://editor.test/');
        await page.addScriptTag({ url: 'http://editor.test/tinymce/tinymce.min.js' });
        const config = fs.readFileSync('resources/views/admin/partials/tinymce-notepad.blade.php', 'utf8').replace(/\{\{--[\s\S]*?--\}\}/g, '');
        await page.addScriptTag({ content: 'window.editorConfig = ' + config + ';' });
        await page.evaluate(() => tinymce.init({ ...editorConfig, selector: '#editor', base_url: 'http://editor.test/tinymce', suffix: '.min', content_css: false }));
        const body = page.frameLocator('iframe').locator('body');
        await body.click();
        await page.keyboard.press('Control+End');
        await page.keyboard.type('ABC');
        await page.keyboard.press('Backspace');
        assert.match(await body.textContent(), /Editable textAB/);
        await page.keyboard.press('Control+Home');
        await page.keyboard.press('Delete');
        assert.match(await body.textContent(), /^ditable textAB/);
        const pasted = await page.evaluate(() => {
            const args = { content: '<p contenteditable="false" class="mceNonEditable">Paste</p>' };
            editorConfig.paste_preprocess(null, args);
            return args.content;
        });
        assert.doesNotMatch(pasted, /contenteditable|mceNonEditable/);
    } finally { await browser.close(); }
});
