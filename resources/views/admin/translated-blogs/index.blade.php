@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Blog Translations</h1>
  </div>
  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="card-title mb-0">All Translated Posts ({{ $translations->total() }})</h5>
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
              <th width="65%">Title</th>
              <th>Language Code</th>
              <th width="20%">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($translations as $translation)
              <tr>
                <td>{{ $translation->title }}</td>
                <td>
                  <span class="badge bg-info text-white">
                    {{ strtoupper($translation->language_code) }}
                  </span>
                </td>
                <td>
                  <!-- NEW VIEW BUTTON - blog-translated.show with language_code, slug & post_id -->
                  <a href="{{ route('blog.translated', [
                      'language_code' => $translation->language_code,
                      'slug'          => $translation->slug
                      
                  ]) }}"
                     class="btn btn-sm btn-success" 
                     target="_blank">
                    <i class="bi bi-eye"></i> View
                  </a>

                  <!-- Delete button (kept exactly as before) -->
                  <form action="{{ route('admin.translated-blogs.destroy', $translation) }}"
                        method="POST" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to delete this translation?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center py-4">No translations found yet</td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <!-- ==================== PAGINATION ==================== -->
        <div class="d-flex justify-content-center mt-4" style="margin-top: 2rem !important;">
          @if ($translations->hasPages())
            <nav aria-label="Blog translations pagination">
              <ul class="pagination custom-page-number-pagination">
                <!-- Previous Arrow -->
                <li class="page-item {{ $translations->onFirstPage() ? 'disabled' : '' }}">
                  <a class="page-link" href="{{ $translations->previousPageUrl() }}" {{ $translations->onFirstPage() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>

                <!-- Page Numbers with ellipsis -->
                @php
                  $current = $translations->currentPage();
                  $last = $translations->lastPage();
                @endphp

                <!-- Always show first page -->
                <li class="page-item {{ $current == 1 ? 'active' : '' }}">
                  <a class="page-link" href="{{ $translations->url(1) }}">1</a>
                </li>

                <!-- Show pages near current page -->
                @for ($i = max(2, $current - 2); $i <= min($last - 1, $current + 2); $i++)
                  <li class="page-item {{ $current == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $translations->url($i) }}">{{ $i }}</a>
                  </li>
                @endfor

                <!-- Ellipsis + Last page -->
                @if ($last > 5 && $current < $last - 3)
                  <li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                  <li class="page-item {{ $current == $last ? 'active' : '' }}">
                    <a class="page-link" href="{{ $translations->url($last) }}">{{ $last }}</a>
                  </li>
                @endif

                <!-- Next Arrow -->
                <li class="page-item {{ $translations->hasMorePages() ? '' : 'disabled' }}">
                  <a class="page-link" href="{{ $translations->nextPageUrl() }}" {{ !$translations->hasMorePages() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
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

  <!-- Advanced & Fully Responsive CSS -->
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