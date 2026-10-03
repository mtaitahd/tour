@extends('admin.layouts.app')
@section('title', 'Manager Lists')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
    <div><h1>Manager Lists</h1><div class="text-muted">Create public, search-friendly tour and page collections.</div></div>
    <a class="btn btn-primary" href="{{ route('admin.manager-lists.create') }}"><i class="bi bi-plus-lg"></i> Add Listing</a>
</div>
<section class="section"><div class="card"><div class="card-body">
    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    <div class="table-responsive mt-3"><table class="table table-hover align-middle">
        <thead><tr><th>Listing</th><th>Content</th><th>Status</th><th>Public URL</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($lists as $list)
            <tr>
                <td><strong>{{ $list->title }}</strong><div class="small text-muted">{{ $list->slug }}</div></td>
                <td>{{ $list->content_type === 'tours' ? 'Tours' : 'Pages' }}</td>
                <td><span class="badge {{ $list->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($list->status) }}</span></td>
                <td>@if($list->status === 'published')<a href="{{ route('manager-lists.show', $list->slug) }}" target="_blank">/collections/{{ $list->slug }} <i class="bi bi-box-arrow-up-right"></i></a>@else<span class="text-muted">Not public</span>@endif</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.manager-lists.edit', $list) }}">Edit</a>
                    <form class="d-inline" method="POST" action="{{ route('admin.manager-lists.destroy', $list) }}" onsubmit="return confirm('Delete this listing?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No managed listings yet. Add your first listing to get started.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $lists->links() }}
</div></div></section>
@endsection
