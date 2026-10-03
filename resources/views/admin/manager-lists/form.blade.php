@extends('admin.layouts.app')
@section('title', $managerList->exists ? 'Edit Listing' : 'Add Listing')

@section('content')
@php
    $editing = $managerList->exists;
    $selectedCategories = collect(old('category_ids', $managerList->category_ids ?? []))->map(fn ($id) => (string) $id)->all();
    $selectedPages = collect(old('page_ids', $managerList->page_ids ?? []))->map(fn ($id) => (string) $id)->all();
    $faqs = old('faqs', $managerList->faqs ?: [['question' => '', 'answer' => '']]);
@endphp
<div class="pagetitle"><h1>{{ $editing ? 'Edit' : 'Create' }} {{ $listType === 'tours' ? 'Tour' : 'Page' }} List</h1><nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ $listType === 'tours' ? route('admin.manager-lists.tours') : route('admin.manager-lists.pages') }}">{{ $listType === 'tours' ? 'Manager Tour Lists' : 'Manager Page Lists' }}</a></li><li class="breadcrumb-item active">{{ $editing ? 'Edit' : 'Create' }}</li></ol></nav></div>
<section class="section"><div class="card"><div class="card-body">
    @if($errors->any())<div class="alert alert-danger mt-3"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form id="manager-list-form" method="POST" action="{{ $editing ? route('admin.manager-lists.update', $managerList) : route('admin.manager-lists.store') }}" class="mt-3">
        @csrf @if($editing) @method('PUT') @endif
        <h5 class="card-title">Listing content</h5>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Title *</label><div class="col-md-10"><input class="form-control" name="title" value="{{ old('title', $managerList->title) }}" required maxlength="255"></div></div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">URL slug</label><div class="col-md-10"><input class="form-control" name="slug" value="{{ old('slug', $managerList->slug) }}" placeholder="Auto-created from title"><small class="text-muted">Public URL: {{ url('/collections') }}/your-slug</small></div></div>
        <input type="hidden" name="content_type" value="{{ $listType }}">
        <div class="alert alert-info"><strong>List type:</strong> {{ $listType === 'tours' ? 'Tours grouped by selected categories' : 'Selected pages only' }}</div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Caption</label><div class="col-md-10"><input class="form-control" name="caption" value="{{ old('caption', $managerList->caption) }}" placeholder="Our Best All Tours & Safaris Packages"></div></div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Introduction</label><div class="col-md-10"><textarea class="form-control tinymce-editor" name="introduction" rows="8">{{ old('introduction', $managerList->introduction) }}</textarea><small class="text-muted">Write the descriptive content shown around the card listing.</small></div></div>

        @if($listType === 'tours')<div class="row mb-3"><label class="col-md-2 col-form-label">Tour categories *</label><div class="col-md-10">
            <div class="border rounded p-3" style="max-height:240px;overflow:auto">@forelse($categories as $category)<label class="d-block mb-2"><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array((string) $category->id, $selectedCategories, true))> {{ $category->name }}</label>@empty<p class="text-muted mb-0">No tour categories exist yet.</p>@endforelse</div>
            <small class="text-muted">The public list combines published tours assigned to any selected category.</small>
        </div></div>
        @else<div class="row mb-3"><label class="col-md-2 col-form-label">Pages to display *</label><div class="col-md-10">
            <div class="border rounded p-3" style="max-height:240px;overflow:auto">@forelse($pages as $page)<label class="d-block mb-2"><input type="checkbox" name="page_ids[]" value="{{ $page->id }}" @checked(in_array((string) $page->id, $selectedPages, true))> {{ $page->title }} <small class="text-muted">/{{ $page->slug }}</small></label>@empty<p class="text-muted mb-0">No published pages are available.</p>@endforelse</div>
            <small class="text-muted">Page lists contain only the pages selected here; tour records are never shown.</small>
        </div></div>@endif

        <hr><div class="d-flex justify-content-between align-items-center"><h5 class="card-title mb-2">Frequently asked questions</h5><button type="button" class="btn btn-sm btn-outline-primary" id="add-faq">Add FAQ</button></div>
        <div id="faq-list">@foreach($faqs as $index => $faq)<div class="border rounded p-3 mb-3 faq-row"><div class="d-flex justify-content-between"><strong>FAQ</strong><button class="btn btn-sm btn-outline-danger remove-faq" type="button">Remove</button></div><div class="mt-2"><label class="form-label">Question</label><input class="form-control" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" maxlength="500"></div><div class="mt-2"><label class="form-label">Answer</label><textarea class="form-control" name="faqs[{{ $index }}][answer]" rows="3">{{ $faq['answer'] ?? '' }}</textarea></div></div>@endforeach</div>

        <hr><h5 class="card-title">Search engine settings</h5>
        <div class="row mb-3"><label class="col-md-2 col-form-label">SEO title</label><div class="col-md-10"><input class="form-control" name="meta_title" value="{{ old('meta_title', $managerList->meta_title) }}" maxlength="255"></div></div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Meta description</label><div class="col-md-10"><textarea class="form-control" name="meta_description" rows="2" maxlength="500">{{ old('meta_description', $managerList->meta_description) }}</textarea></div></div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Keywords</label><div class="col-md-10"><input class="form-control" name="meta_keywords" value="{{ old('meta_keywords', $managerList->meta_keywords) }}"></div></div>
        <div class="row mb-3"><label class="col-md-2 col-form-label">Status / order</label><div class="col-md-5"><select class="form-select" name="status"><option value="draft" @selected(old('status', $managerList->status) === 'draft')>Draft</option><option value="published" @selected(old('status', $managerList->status) === 'published')>Published</option></select></div><div class="col-md-5"><input type="number" min="0" class="form-control" name="order" value="{{ old('order', $managerList->order ?? 0) }}" placeholder="Display order"></div></div>
        <div class="form-check mb-4"><input type="hidden" name="no_robots" value="0"><input class="form-check-input" type="checkbox" name="no_robots" value="1" id="no-robots" @checked(old('no_robots', $managerList->no_robots))><label class="form-check-label" for="no-robots">Keep this listing out of search engines</label></div>
        <div class="d-flex gap-2"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save List' : 'Create List' }}</button><a class="btn btn-outline-secondary" href="{{ $listType === 'tours' ? route('admin.manager-lists.tours') : route('admin.manager-lists.pages') }}">Cancel</a></div>
    </form>
</div></div></section>
<template id="faq-template"><div class="border rounded p-3 mb-3 faq-row"><div class="d-flex justify-content-between"><strong>FAQ</strong><button class="btn btn-sm btn-outline-danger remove-faq" type="button">Remove</button></div><div class="mt-2"><label class="form-label">Question</label><input class="form-control" data-name="question" maxlength="500"></div><div class="mt-2"><label class="form-label">Answer</label><textarea class="form-control" data-name="answer" rows="3"></textarea></div></div></template>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('faq-list');
    document.getElementById('add-faq').addEventListener('click', function () {
        const index = list.querySelectorAll('.faq-row').length;
        const row = document.getElementById('faq-template').content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[data-name]').forEach(input => input.name = 'faqs[' + index + '][' + input.dataset.name + ']'); list.appendChild(row);
    });
    list.addEventListener('click', event => { if (event.target.closest('.remove-faq')) { event.target.closest('.faq-row').remove(); } });
});
</script>
@endsection
