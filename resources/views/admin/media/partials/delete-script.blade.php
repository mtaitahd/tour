const mediaGrid = document.querySelector('.media-grid');
const selectPage = document.getElementById('select-page-media');
const deleteSelected = document.getElementById('delete-selected-media');
const includeUsed = document.getElementById('include-used-media');
const deleteResults = document.getElementById('bulk-delete-results');
let deletingMedia = false;

function mediaSelections() {
    return Array.from(document.querySelectorAll('.select-media'));
}

function updateMediaSelection() {
    const boxes = mediaSelections();
    const count = boxes.filter(box => box.checked).length;
    if (!selectPage) return;
    selectPage.checked = count > 0 && count === boxes.length;
    selectPage.indeterminate = count > 0 && count < boxes.length;
    document.getElementById('selected-media-count').textContent = `${count} selected`;
    deleteSelected.disabled = deletingMedia || count === 0;
}

async function removeMedia(url, force = false) {
    const response = await fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(force ? { force: true, confirm_used: true } : {}),
    });
    const data = await response.json();
    return { ok: response.ok && data.success === true, ...data };
}

selectPage?.addEventListener('change', () => {
    mediaSelections().forEach(box => { box.checked = selectPage.checked; });
    updateMediaSelection();
});

mediaGrid?.addEventListener('change', event => {
    if (event.target.matches('.select-media')) updateMediaSelection();
});

deleteSelected?.addEventListener('click', async () => {
    if (deletingMedia) return;
    const selected = mediaSelections().filter(box => box.checked);
    if (!selected.length) return;
    const force = includeUsed?.checked === true;
    const used = selected.filter(box => box.dataset.used === '1').length;
    const warning = force
        ? `${used} selected image(s) are in use. Their blog posts, tours or pages may lose these images.`
        : 'Images that are in use will be kept.';
    if (!confirm(`Permanently delete ${selected.length} selected image(s)? ${warning} This cannot be undone. New or unselected images will not be deleted.`)) return;

    deletingMedia = true;
    const controls = Array.from(document.querySelectorAll('.select-media, .remove-media, #select-page-media, #include-used-media, #delete-selected-media'));
    controls.forEach(control => { control.disabled = true; });
    deleteResults.replaceChildren();
    const progress = document.createElement('p');
    deleteResults.append(progress);
    let deleted = 0;
    let failed = 0;
    try {
        for (const [index, box] of selected.entries()) {
            progress.textContent = `Deleting ${index + 1} of ${selected.length}…`;
            try {
                const result = await removeMedia(box.dataset.url, force);
                if (!result.ok) throw new Error(result.message || 'Image could not be deleted.');
                deleted++;
                box.closest('[class*="col-"]').remove();
            } catch (error) {
                failed++;
                const message = document.createElement('p');
                message.className = 'text-danger mb-1';
                message.textContent = `${box.dataset.name}: ${error.message}`;
                deleteResults.append(message);
            }
        }
    } finally {
        deletingMedia = false;
        controls.forEach(control => { control.disabled = false; });
        updateMediaSelection();
        progress.textContent = `${deleted} deleted. ${failed} kept or failed. Unselected images were kept.`;
        if (deleted) {
            const refresh = document.createElement('a');
            const url = new URL(window.location.href);
            url.searchParams.delete('page');
            refresh.href = url.toString();
            refresh.textContent = 'Refresh library counts and pages';
            deleteResults.append(refresh);
        }
    }
});

mediaGrid?.addEventListener('click', async event => {
    const button = event.target.closest('.remove-media');
    if (!button || deletingMedia) return;
    event.preventDefault();
    if (!confirm('Delete this image permanently? This cannot be undone.')) return;
    button.disabled = true;
    try {
        let result = await removeMedia(button.dataset.url);
        if (!result.ok && result.can_force_delete
            && confirm('This image is in use. Permanently delete it anyway? Blog posts, tours or pages using it may lose their image. This cannot be undone.')) {
            result = await removeMedia(button.dataset.url, true);
        }
        if (result.ok) {
            button.closest('[class*="col-"]').remove();
            updateMediaSelection();
            showToast('Image deleted.', 'success');
        } else {
            showToast(result.message || 'Image could not be deleted.', 'danger');
        }
    } catch {
        showToast('Delete request failed. Refresh to check the image before trying again.', 'danger');
    } finally {
        button.disabled = false;
    }
});
