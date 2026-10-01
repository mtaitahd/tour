<script>
(() => {
  const form = document.getElementById('page-builder-form');
  if (!form) return;

  const panels = Array.from(form.querySelectorAll('[data-page-step]'));
  const indicators = Array.from(form.querySelectorAll('[data-page-step-indicator]'));
  const progress = document.getElementById('page-wizard-progress');
  const saveStatus = document.getElementById('page-draft-status');
  const draftIdInput = document.getElementById('page-draft-id');
  const methodInput = document.getElementById('page-method');
  const autosaveEnabled = @json((bool) $autosaveEnabled);
  const autosaveUrl = @json(route('admin.pages.autosave'));
  let activeStep = 1;
  let saveTimer = null;
  let saveInProgress = null;
  let lastSavedData = '';

  function setStep(step) {
    activeStep = Math.max(1, Math.min(2, Number(step)));
    panels.forEach(panel => panel.classList.toggle('d-none', Number(panel.dataset.pageStep) !== activeStep));
    indicators.forEach(button => {
      const selected = Number(button.dataset.pageStepIndicator) === activeStep;
      button.classList.toggle('btn-success', selected);
      button.classList.toggle('btn-outline-secondary', !selected);
      if (selected) button.setAttribute('aria-current', 'step');
      else button.removeAttribute('aria-current');
    });
    if (progress) progress.style.width = activeStep === 1 ? '50%' : '100%';
  }

  form.querySelectorAll('.page-step-next').forEach(button => button.addEventListener('click', async () => {
    if (autosaveEnabled) await saveDraft();
    setStep(2);
  }));
  form.querySelectorAll('.page-step-back').forEach(button => button.addEventListener('click', () => setStep(1)));
  indicators.forEach(button => button.addEventListener('click', () => setStep(button.dataset.pageStepIndicator)));

  if (!autosaveEnabled) return;

  function prepareFormData() {
    if (window.tinymce) window.tinymce.triggerSave();
    const data = new FormData(form);
    data.delete('_method');
    data.set('draft_id', draftIdInput ? draftIdInput.value : '');
    return data;
  }

  function dataSignature(data) {
    return JSON.stringify(Array.from(data.entries()).map(([key, value]) => [key, value instanceof File ? `${value.name}:${value.size}:${value.lastModified}` : value]));
  }

  async function saveDraft(force = false) {
    if (!autosaveEnabled) return true;
    if (saveInProgress) await saveInProgress;
    const data = prepareFormData();
    const signature = dataSignature(data);
    if (!force && signature === lastSavedData) return true;
    if (saveStatus) saveStatus.textContent = 'Saving draft…';

    saveInProgress = (async () => {
      try {
        const response = await fetch(autosaveUrl, {
          method: 'POST', body: data, credentials: 'same-origin',
          headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.draft_id) throw new Error(result.message || 'Could not save this page draft. Check your connection and try again.');
        if (draftIdInput) draftIdInput.value = result.draft_id;
        const slugInput = form.querySelector('[name="slug"]');
        if (slugInput && !slugInput.value.trim()) slugInput.value = result.slug;
        if (methodInput) methodInput.value = 'PUT';
        if (form.dataset.updateUrl) form.action = form.dataset.updateUrl.replace('__PAGE_ID__', result.draft_id);
        lastSavedData = signature;
        if (saveStatus) {
          saveStatus.textContent = `Draft saved at ${new Date(result.saved_at || Date.now()).toLocaleTimeString()}`;
          saveStatus.classList.remove('text-danger');
          saveStatus.classList.add('text-success');
        }
        return true;
      } catch (error) {
        if (saveStatus) {
          saveStatus.textContent = error.message;
          saveStatus.classList.remove('text-success');
          saveStatus.classList.add('text-danger');
        }
        return false;
      } finally {
        saveInProgress = null;
      }
    })();
    return saveInProgress;
  }

  function scheduleSave() {
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveDraft(), 900);
  }

  form.addEventListener('input', scheduleSave);
  form.addEventListener('change', scheduleSave);
  form.addEventListener('click', event => {
    if (event.target.closest('.remove-counter')) setTimeout(scheduleSave, 0);
  });
  if (window.tinymce) {
    window.tinymce.on('AddEditor', event => {
      event.editor.on('input change undo redo', scheduleSave);
    });
    window.tinymce.editors.forEach(editor => editor.on('input change undo redo', scheduleSave));
  }

  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (saveTimer) clearTimeout(saveTimer);
    const titleInput = form.querySelector('[name="title"]');
    if (titleInput && !titleInput.value.trim()) {
      setStep(1);
      titleInput.focus();
      titleInput.reportValidity();
      return;
    }
    if (saveStatus && saveStatus.classList.contains('text-danger')) {
      const retry = await saveDraft(true);
      if (!retry) return;
    } else {
      const saved = await saveDraft(true);
      if (!saved) return;
    }
    form.submit();
  });

  setStep(1);
})();
</script>
