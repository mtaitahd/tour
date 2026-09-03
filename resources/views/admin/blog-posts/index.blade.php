@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Blog Posts</h1>
  </div>
  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Categories ({{ $posts->total() }})</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#blogFormModal" onclick="openBlogForm('{{ route('admin.blog-posts.create') }}')">
                <i class="bi bi-plus-circle"></i>Add New Post
              </button>
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
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Published</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($posts as $post)
              <tr>
                <td>{{ $post->title }}</td>
                <td>{{ $post->category->name ?? '-' }}</td>
                <td>
                  <span class="badge bg-{{ $post->status == 'published' ? 'success' : 'warning' }}">
                    {{ ucfirst($post->status) }}
                  </span>
                </td>
                <td>{{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}</td>
                <td>
                  <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-sm btn-success" target="_blank">View</a>
                  <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#blogFormModal" onclick="openBlogForm('{{ route('admin.blog-posts.edit', $post) }}')">
                    Edit
                  </button>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No posts yet</td></tr>
            @endforelse
          </tbody>
        </table>

        <!-- ==================== CLEAN PAGINATION - ONLY PAGE NUMBERS ==================== -->
        <div class="d-flex justify-content-center mt-4" style="margin-top: 2rem !important;">

          @if ($posts->hasPages())
            <nav aria-label="Blog posts pagination">
              <ul class="pagination custom-page-number-pagination">

                <!-- Previous Arrow -->
                <li class="page-item {{ $posts->onFirstPage() ? 'disabled' : '' }}">
                  <a class="page-link" href="{{ $posts->previousPageUrl() }}" {{ $posts->onFirstPage() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>

                <!-- ONLY PAGE NUMBERS (1, 2, 3, 4 ...) -->
                @for ($i = 1; $i <= $posts->lastPage(); $i++)
                  <li class="page-item {{ $posts->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $posts->url($i) }}">{{ $i }}</a>
                  </li>
                @endfor

                <!-- Next Arrow -->
                <li class="page-item {{ $posts->hasMorePages() ? '' : 'disabled' }}">
                  <a class="page-link" href="{{ $posts->nextPageUrl() }}" {{ !$posts->hasMorePages() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </li>

              </ul>
            </nav>
          @endif

        </div>
        <!-- ==================== END PAGINATION ==================== -->

      </div>
    </div>
  </section>

  <!-- Add / Edit Blog Post Modal -->
  <div class="modal fade" id="blogFormModal" tabindex="-1" aria-labelledby="blogFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="blogFormModalLabel">Blog Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="blogFormIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <!-- Advanced & Fully Responsive CSS - Only Page Numbers Style -->
  <style>
    .custom-page-number-pagination {
      flex-wrap: wrap;
      gap: 6px;
      justify-content: center;
      padding: 0;
      margin: 0 auto;
      list-style: none;
    }

    .custom-page-number-pagination .page-item {
      margin-bottom: 6px;
    }

    .custom-page-number-pagination .page-link {
      border: none !important;
      border-radius: 50px !important;
      padding: 12px 18px !important;
      font-size: 1rem;
      font-weight: 700;
      color: #495057;
      background-color: #f8f9fa;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
      transition: all 0.3s ease;
      min-width: 48px;
      text-align: center;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .custom-page-number-pagination .page-link:hover {
      background-color: #e9ecef;
      color: #0d6efd;
      transform: translateY(-3px) scale(1.08);
      box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
    }

    .custom-page-number-pagination .page-item.active .page-link {
      background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important;
      color: #fff !important;
      box-shadow: 0 8px 20px rgba(13, 110, 253, 0.5) !important;
      transform: scale(1.1);
    }

    .custom-page-number-pagination .page-item.disabled .page-link {
      background-color: #f1f3f5 !important;
      color: #adb5bd !important;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }

    /* Mobile friendly */
    @media (max-width: 576px) {
      .custom-page-number-pagination .page-link {
        padding: 10px 14px !important;
        min-width: 40px;
        font-size: 0.95rem;
      }
      .custom-page-number-pagination {
        gap: 4px;
      }
    }
  </style>
@endsection

@push('scripts')
  <script>
    function openBlogForm(url) {
      var frame = document.getElementById('blogFormIframe');
      document.getElementById('blogFormModalLabel').textContent = 'Add Blog Post';
      if (typeof url === 'string' && url.indexOf('/edit') !== -1) {
        document.getElementById('blogFormModalLabel').textContent = 'Edit Blog Post';
      }
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      frame.src = url + sep + 'modal=1';
    }

    (function () {
      var modalEl = document.getElementById('blogFormModal');
      var frame = document.getElementById('blogFormIframe');
      var wasForm = false;

      modalEl.addEventListener('hidden.bs.modal', function () {
        wasForm = false;
        frame.src = 'about:blank';
      });

      frame.addEventListener('load', function () {
        try {
          var path = frame.contentWindow.location.pathname;
          var basePath = new URL("{{ route('admin.blog-posts.index') }}", window.location.origin).pathname;
          if (path === basePath && wasForm) {
            wasForm = false;
            var inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
            window.location.reload();
            return;
          }
          if (path !== basePath && path.indexOf(basePath + '/') === 0) {
            wasForm = true;
          } else {
            wasForm = false;
          }
        } catch (e) {
          wasForm = false;
        }
      });
    })();
  </script>
@endpush