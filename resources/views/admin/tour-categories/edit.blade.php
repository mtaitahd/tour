@extends('admin.layouts.app')
@section('title', 'Edit Tour Category')

@section('content')
  <div class="pagetitle">
    <h1>Edit Tour Category</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/ </a></li>
        <li><a href="{{ route('admin.tour-categories.index') }}"> Tour Categories</a></li>
        <li class="breadcrumb-item active">/ Edit</li>
      </ol>
    </nav>
  </div>

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
      <div class="col-lg-10">
        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ route('admin.tour-categories.update', $tourCategory) }}">
              @csrf
              @method('PUT')

              <div class="row my-3">
                <label class="col-sm-3 col-form-label">Category Name *</label>
                <div class="col-sm-9">
                  <input type="text" name="name" class="form-control" required
                         value="{{ old('name', $tourCategory->name) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Slug</label>
                <div class="col-sm-9">
                  <input type="text" name="slug" class="form-control"
                         value="{{ old('slug', $tourCategory->slug) }}">
                  <small class="text-muted">Changing this changes the public URL (currently <code>/{{ $tourCategory->slug }}</code>) — existing links/bookmarks to the old slug will stop working.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                  <textarea name="description" class="form-control" rows="4">{{ old('description', $tourCategory->description) }}</textarea>
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Order</label>
                <div class="col-sm-9">
                  <input type="number" name="order" class="form-control w-25"
                         value="{{ old('order', $tourCategory->order) }}" min="0">
                </div>
              </div>

              <!-- SEO Fields -->
              <div class="row mt-4 mb-2">
                <div class="col-12"><h6 class="fw-bold text-muted">SEO Settings</h6><hr></div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Title</label>
                <div class="col-sm-9">
                  <input type="text" name="meta_title" class="form-control"
                         value="{{ old('meta_title', $tourCategory->meta_title) }}"
                         placeholder="e.g. Tanzania Tours & Safaris | Afro-Vertex" maxlength="255">
                  <small class="text-muted">Max 60 chars recommended. Shown in Google search results.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Description</label>
                <div class="col-sm-9">
                  <textarea name="meta_description" class="form-control" rows="3"
                            placeholder="Discover the best Tanzania tours, safaris, and adventures...">{{ old('meta_description', $tourCategory->meta_description) }}</textarea>
                  <small class="text-muted">Max 155 chars recommended. Shown below the title in Google results.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Keywords</label>
                <div class="col-sm-9">
                  <input type="text" name="meta_keywords" class="form-control"
                         value="{{ old('meta_keywords', $tourCategory->meta_keywords) }}"
                         placeholder="tanzania tours, safari, kilimanjaro" maxlength="255">
                  <small class="text-muted">Comma-separated keywords.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Hide from Search Engines</label>
                <div class="col-sm-9">
                  <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="no_robots" value="1"
                           id="no_robots" {{ old('no_robots', $tourCategory->no_robots) ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_robots">Noindex — exclude from Google / sitemap</label>
                  </div>
                </div>
              </div>

              <!-- Content Sections -->
              <div class="row mt-5">
                <label class="col-sm-3 col-form-label fw-bold">Content Sections</label>
                <div class="col-sm-9">
                  <p class="text-muted small">
                    Freeform sections shown on this category's public listing page
                    (e.g. "Overview", "Why Choose {{ $tourCategory->name }}", "Who
                    This Is For"). Each can appear above or below the tour package
                    grid, in the order shown here.
                  </p>

                  <div id="category-sections-repeater">
                    @php $existingSections = old('sections', $tourCategory->sections->map(fn ($s) => [
                        'title' => $s->title,
                        'content' => $s->content,
                        'existing_image_id' => $s->image_id,
                        'placement' => $s->placement,
                    ])->all()); @endphp

                    @foreach ($existingSections as $index => $section)
                      <div class="section-item card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                          <h6 class="mb-0">Section {{ $loop->iteration }}</h6>
                          <button type="button" class="btn btn-sm btn-danger remove-section">
                            <i class="bi bi-trash"></i> Remove
                          </button>
                        </div>
                        <div class="card-body">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label">Section Title</label>
                              <input type="text" name="sections[{{ $index }}][title]" class="form-control"
                                     value="{{ old("sections.$index.title", $section['title'] ?? '') }}"
                                     placeholder="e.g. Overview">
                            </div>
                            <div class="col-md-6">
                              <label class="form-label">Placement</label>
                              @php $sectionPlacement = old("sections.$index.placement", $section['placement'] ?? 'below_grid'); @endphp
                              <select name="sections[{{ $index }}][placement]" class="form-select">
                                <option value="above_grid" {{ $sectionPlacement === 'above_grid' ? 'selected' : '' }}>Above the tour grid</option>
                                <option value="below_grid" {{ $sectionPlacement === 'below_grid' ? 'selected' : '' }}>Below the tour grid</option>
                              </select>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label">Section Image</label>
                              @php $sectionExistingImageId = old("sections.$index.existing_image_id", $section['existing_image_id'] ?? null); @endphp
                              <x-media-picker
                                  name="sections[{{ $index }}][existing_image_id]"
                                  :selected="$sectionExistingImageId"
                                  label="Select Section Image"
                              />
                            </div>
                            <div class="col-md-12">
                              <label class="form-label">Content</label>
                              <textarea name="sections[{{ $index }}][content]" class="form-control" rows="4">{{ old("sections.$index.content", $section['content'] ?? '') }}</textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>

                  <button type="button" id="add-category-section" class="btn btn-outline-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Add Section
                  </button>

                  {{-- Hidden template picker for new sections added client-side — same
                       clone-and-rename approach used in tour-packages/edit.blade.php's
                       itinerary day picker template. --}}
                  <div id="category-section-picker-template" class="d-none">
                    <x-media-picker
                        name="sections[__INDEX__][existing_image_id]"
                        :selected="null"
                        label="Select Section Image"
                    />
                  </div>
                </div>
              </div>

              <div class="row mb-3 mt-4">
                <label class="col-sm-3 col-form-label"></label>
                <div class="col-sm-9">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <a href="{{ route('admin.tour-categories.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        let sectionIndex = document.querySelectorAll('#category-sections-repeater .section-item').length;

        document.getElementById('add-category-section').addEventListener('click', function () {
            const newSection = document.createElement('div');
            newSection.className = 'section-item card mb-3 shadow-sm';
            newSection.innerHTML = `
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h6 class="mb-0">Section ${sectionIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-section"><i class="bi bi-trash"></i> Remove</button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="sections[${sectionIndex}][title]" class="form-control" placeholder="e.g. Overview">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placement</label>
                            <select name="sections[${sectionIndex}][placement]" class="form-select">
                                <option value="above_grid">Above the tour grid</option>
                                <option value="below_grid" selected>Below the tour grid</option>
                            </select>
                        </div>
                        <div class="col-md-6 section-picker-slot">
                            <label class="form-label">Section Image</label>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Content</label>
                            <textarea name="sections[${sectionIndex}][content]" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>`;
            document.getElementById('category-sections-repeater').appendChild(newSection);

            // Clone the hidden template picker for this new section — same approach
            // as tour-packages/edit.blade.php's itinerary day picker template.
            const templateHtml = document.getElementById('category-section-picker-template').innerHTML;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = templateHtml.replace(/__INDEX__/g, sectionIndex);
            const pickerNode = wrapper.firstElementChild;
            newSection.querySelector('.section-picker-slot').appendChild(pickerNode);
            if (typeof Sortable !== 'undefined') {
                pickerNode.querySelectorAll('.media-picker-sortable').forEach(function (el) {
                    new Sortable(el, { animation: 150, ghostClass: 'bg-light' });
                });
            }

            sectionIndex++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-section')) {
                e.target.closest('.section-item').remove();
            }
        });
    });
  </script>
  @endpush
@endsection
