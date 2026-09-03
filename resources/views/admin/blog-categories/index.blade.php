@extends('admin.layouts.app')
@section('title', 'Blog Categories')

@section('content')
  <div class="pagetitle">
    <h1>Blog Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Blog Categories</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Categories ({{ $categories->total() }})</h5>
              <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Category
              </a>
            </div>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Slug</th>
                  <th>Posts</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($categories as $category)
                  <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->posts()->count() }}</td>
                    <td>
                      <a href="{{ route('admin.blog-categories.edit', $category) }}" class="btn btn-sm btn-warning">Edit</a>
                      <form action="{{ route('admin.blog-categories.destroy', $category) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Posts will remain but lose category.')">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-4">No categories yet. Add your first one!</td>
                  </tr>
                @endforelse
              </tbody>
            </table>

            {{ $categories->links() }}
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection