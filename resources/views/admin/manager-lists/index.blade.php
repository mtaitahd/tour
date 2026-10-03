@extends('admin.layouts.app')
@section('title', $listType === 'tours' ? 'Manager Tour Lists' : 'Manager Page Lists')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
    <div><h1>{{ $listType === 'tours' ? 'Manager Tour Lists' : 'Manager Page Lists' }}</h1><div class="text-muted">{{ $listType === 'tours' ? 'Build public tour listings from selected categories.' : 'Build public listings using selected pages only.' }}</div></div>
    <a class="btn btn-primary" href="{{ route('admin.manager-lists.create', ['type' => $listType]) }}" data-manager-list-modal-url="{{ route('admin.manager-lists.create', ['type' => $listType]) }}" data-modal-title="Add {{ $listType === 'tours' ? 'Tour' : 'Page' }} List"><i class="bi bi-plus-lg"></i> Add {{ $listType === 'tours' ? 'Tour' : 'Page' }} List</a>
</div>
<section class="section"><div class="card"><div class="card-body">
    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    <div class="table-responsive mt-3"><table class="table table-hover align-middle">
        <thead><tr><th>Listing</th><th>Status</th><th>Public URL</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($lists as $list)
            <tr>
                <td><strong>{{ $list->title }}</strong><div class="small text-muted">{{ $list->slug }}</div></td>
                <td><span class="badge {{ $list->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($list->status) }}</span></td>
                <td>@if($list->status === 'published')<a href="{{ route('manager-lists.show', $list->slug) }}" target="_blank">/collections/{{ $list->slug }} <i class="bi bi-box-arrow-up-right"></i></a>@else<span class="text-muted">Not public</span>@endif</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.manager-lists.edit', $list) }}" data-manager-list-modal-url="{{ route('admin.manager-lists.edit', $list) }}" data-modal-title="Edit {{ $listType === 'tours' ? 'Tour' : 'Page' }} List">Edit</a>
                    <form class="d-inline" method="POST" action="{{ route('admin.manager-lists.destroy', $list) }}" onsubmit="return confirm('Delete this listing?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">No {{ $listType === 'tours' ? 'tour' : 'page' }} lists yet. Add your first list to get started.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $lists->links() }}
</div></div></section>
<div class="modal fade" id="manager-list-modal" tabindex="-1" aria-labelledby="manager-list-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="manager-list-modal-title">Manage Listing</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div class="text-center py-5"><span class="spinner-border text-primary" aria-hidden="true"></span><span class="visually-hidden">Loading form</span></div></div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('manager-list-modal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const modalBody = modalElement.querySelector('.modal-body');
    const modalTitle = document.getElementById('manager-list-modal-title');

    document.addEventListener('click', async function (event) {
        const trigger = event.target.closest('[data-manager-list-modal-url]');
        if (!trigger) return;
        event.preventDefault();
        modalTitle.textContent = trigger.dataset.modalTitle || 'Manage Listing';
        modalBody.innerHTML = '<div class="text-center py-5"><span class="spinner-border text-primary" role="status"></span><div class="mt-2">Loading form…</div></div>';
        modal.show();
        try {
            const response = await fetch(trigger.dataset.managerListModalUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Could not load the form. Please try again.');
            const documentFragment = new DOMParser().parseFromString(await response.text(), 'text/html');
            const form = documentFragment.querySelector('#manager-list-form');
            if (!form) throw new Error('The listing form was not found.');
            modalBody.replaceChildren(form);
            const faqTemplate = documentFragment.querySelector('#faq-template');
            if (faqTemplate) modalBody.append(faqTemplate);
            if (window.tinymce) {
                tinymce.init(Object.assign({
                    selector: '#manager-list-modal .tinymce-editor',
                    setup: function (editor) { editor.on('change', function () { editor.save(); }); }
                }, @include('admin.partials.tinymce-notepad')));
            }
        } catch (error) {
            modalBody.innerHTML = '<div class="alert alert-danger mb-0">' + error.message + '</div>';
        }
    });

    modalElement.addEventListener('click', function (event) {
        if (event.target.closest('.remove-faq')) event.target.closest('.faq-row')?.remove();
        if (event.target.closest('#add-faq')) {
            const list = modalBody.querySelector('#faq-list');
            const template = modalBody.querySelector('#faq-template');
            const index = list.querySelectorAll('.faq-row').length;
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-name]').forEach(input => input.name = 'faqs[' + index + '][' + input.dataset.name + ']');
            list.append(row);
        }
        if (event.target.closest('a.btn-outline-secondary')) { event.preventDefault(); modal.hide(); }
    });

    modalBody.addEventListener('submit', async function (event) {
        const form = event.target.closest('#manager-list-form');
        if (!form) return;
        event.preventDefault();
        if (window.tinymce) tinymce.triggerSave();
        let submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;
        const buttonText = submitButton.textContent;
        submitButton.textContent = 'Saving…';
        modalBody.querySelector('.manager-list-validation-errors')?.remove();
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            });
            const result = await response.json();
            if (response.status === 422) {
                const messages = Object.values(result.errors || {}).flat();
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger manager-list-validation-errors';
                alert.innerHTML = '<strong>Please fix these items:</strong><ul class="mb-0 mt-2">' + messages.map(message => '<li>' + String(message).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char])) + '</li>').join('') + '</ul>';
                form.prepend(alert);
                alert.scrollIntoView({block: 'nearest'});
                return;
            }
            if (!response.ok) throw new Error(result.message || 'Could not save this listing.');
            window.location.href = result.redirect;
        } catch (error) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger manager-list-validation-errors';
            alert.textContent = error.message;
            form.prepend(alert);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = buttonText;
        }
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        if (window.tinymce) tinymce.remove('#manager-list-modal .tinymce-editor');
        modalBody.innerHTML = '';
    });
});
</script>
@endsection
