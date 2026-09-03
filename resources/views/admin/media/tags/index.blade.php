@extends('admin.layouts.app')
@section('title', 'Media Tags')

@section('content')
  <div class="pagetitle">
    <h1>Media Tags</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item active">Tags</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-4">All Tags</h5>

            @if ($tags->isEmpty())
              <p class="text-muted mb-0">No tags yet. Add one to start tagging images — for example "Lion", "Safari", "Sunrise".</p>
            @else
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Used by</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($tags as $tag)
                      <tr>
                        <td>
                          <form action="{{ route('admin.media.tags.update', $tag) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $tag->name }}" class="form-control form-control-sm" style="max-width: 200px;">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                          </form>
                        </td>
                        <td>{{ $tag->media_count }} image{{ $tag->media_count === 1 ? '' : 's' }}</td>
                        <td class="text-end">
                          <form action="{{ route('admin.media.tags.destroy', $tag) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete tag &quot;{{ $tag->name }}&quot;? Images tagged with it will not be deleted, just untagged.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-3">Add Tag</h6>
            <form action="{{ route('admin.media.tags.store') }}" method="POST">
              @csrf
              <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Tag name" required>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Tag
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
