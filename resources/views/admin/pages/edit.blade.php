@extends('admin.layouts.app')
@section('title', 'Edit Page')

@section('content')
  @php
      // When this form is reached from the Site Information screen, the save and
      // the Cancel button both point back at that screen instead of the generic
      // pages list, so the user never lands somewhere that no longer contains
      // the page they just edited. $editingSiteInfo is set by SitePageController.
      $editingSiteInfo = $editingSiteInfo ?? false;
      $backUrl = $editingSiteInfo
          ? route('admin.site-pages.index')
          : route('admin.pages.index');
      $formAction = $editingSiteInfo
          ? route('admin.site-pages.update', $page->slug)
          : route('admin.pages.update', $page->id);
  @endphp
  <div class="pagetitle">
    <h1>Edit Page</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        @if ($editingSiteInfo)
          <li class="breadcrumb-item"><a href="{{ $backUrl }}">Site Information</a></li>
        @else
          <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
        @endif
        <li class="breadcrumb-item active">Edit: {{ Str::limit($page->title, 30) }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div><h5 class="card-title mb-1">Edit Page: {{ $page->title }}</h5><div class="text-muted small">Page information and search settings are organized into two steps.</div></div>
              @if (!$editingSiteInfo)<div class="small text-success" id="page-draft-status" aria-live="polite">{{ $page->draft_payload ? 'Draft restored' : 'Changes save automatically' }}</div>@endif
            </div>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <form id="page-builder-form" method="POST" action="{{ $formAction }}" data-update-url="{{ route('admin.pages.update', ['page' => $page->id]) }}" data-autosave-enabled="{{ $editingSiteInfo ? '0' : '1' }}" enctype="multipart/form-data" novalidate>
              @csrf
              @method('PUT')
              @if (!$editingSiteInfo)<input type="hidden" name="draft_id" id="page-draft-id" value="{{ old('draft_id', $page->id) }}">@endif

              <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-success" id="page-wizard-progress" style="width:50%" role="progressbar"></div></div>
              <div class="row g-2 mb-4">
                <div class="col-6"><button type="button" class="btn btn-success btn-sm w-100 page-step-indicator" data-page-step-indicator="1">1 · Page Information</button></div>
                <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 page-step-indicator" data-page-step-indicator="2">2 · SEO Settings</button></div>
              </div>

              <div class="page-step-panel" data-page-step="1">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" 
                         value="{{ old('title', $page->title) }}" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" 
                         value="{{ old('slug', $page->slug) }}" required>
                  <small class="text-muted">Change only if necessary — affects the public URL</small>
                </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Hero Image</label>
                  <div class="col-sm-10">
                      <div class="mb-3">
                        <x-media-picker
                            name="hero_image_id"
                            :selected="old('hero_image_id', $page->hero_image_id)"
                            label="Select from Media Library"
                        />
                        <small class="text-muted d-block mt-1">Choose an existing image from the library.</small>
                      </div>

                      {{--
                          Legacy: pages saved before the Media Library picker existed
                          still have their hero in the Spatie 'hero' collection rather
                          than hero_image_id. Shown read-only so those pages keep
                          rendering, but it can no longer be changed from here — pick
                          a library image above to take over.
                      --}}
                      @if(!$page->hero_image_id && $page->getFirstMedia('hero'))
                          <div class="mb-2">
                              <img src="{{ $page->getFirstMediaUrl('hero', 'thumb') }}"
                                   alt="Current Hero"
                                   class="img-thumbnail"
                                   style="max-height: 180px;">
                              <small class="text-muted d-block mt-1">
                                  Legacy uploaded image (read-only). Select a Media Library image above to replace it.
                              </small>
                          </div>
                      @endif
                  </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Content</label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control tinymce-editor" rows="12">
                      {{ old('content', $page->content ?? '') }}
                  </textarea>
                </div>
              </div>

              <!-- After the main content textarea -->
              @if(in_array($page->slug, ['contact', 'contact-us']))
                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Contact Page Heading</label>
                      <div class="col-sm-9">
                          <input type="text" name="contact_heading" class="form-control" 
                                 value="{{ old('contact_heading', $page->contact_heading) }}">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Contact Subheading</label>
                      <div class="col-sm-9">
                          <input type="text" name="contact_subheading" class="form-control" 
                                 value="{{ old('contact_subheading', $page->contact_subheading) }}">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Google Maps Embed Code</label>
                      <div class="col-sm-9">
                          <textarea name="contact_map_embed" class="form-control" rows="6">{{ old('contact_map_embed', $page->contact_map_embed) }}</textarea>
                          <small class="text-muted">
                              Paste the full &lt;iframe&gt; code from Google Maps → Share → Embed a map.
                              Leave blank to keep the default map.
                          </small>
                      </div>
                  </div>
              @endif

              @if(in_array($page->slug, ['about', 'about-us']))
                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Story Title</label>
                      <div class="col-sm-9">
                          <input type="text" name="story_title" class="form-control" 
                                 value="{{ old('story_title', $page->story_title) }}">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Story Image</label>
                      <div class="col-sm-9">
                          <div class="mb-3">
                            <x-media-picker
                                name="story_image_id"
                                :selected="old('story_image_id', $page->story_image_id)"
                                label="Select from Media Library"
                            />
                            <small class="text-muted d-block mt-1">Choose an existing image from the library.</small>
                          </div>

                          {{--
                              Fixed: this previously referenced $page->story_image, a plain
                              string column that no longer exists (dropped by migration
                              2026_02_11_213107_remove_legacy_image_columns_from_pages_table —
                              see Phase 1 analysis report). The model stores its story image
                              via the Spatie 'story' media collection instead, so the preview
                              below uses that, matching how the Hero Image section above
                              already does it correctly.
                          --}}
                          @if(!$page->story_image_id && $page->getFirstMedia('story'))
                              <div class="mt-2">
                                  <img src="{{ $page->getFirstMediaUrl('story', 'thumb') }}" alt="Story Image" style="max-height: 150px;" class="img-thumbnail">
                                  <small class="text-muted d-block mt-1">
                                      Legacy uploaded image (read-only). Select a Media Library image above to replace it.
                                  </small>
                              </div>
                          @endif
                      </div>
                  </div>

                  <div class="row mb-3 mt-4">
                      <label class="col-sm-3 col-form-label fw-bold">Story Images (Our Story)</label>
                      <div class="col-sm-9">
                          <small class="text-muted d-block mb-3">
                              Add the images for the Our Story section, each with an optional caption.
                              Use the slots below; leave a slot's image blank to skip it — blank slots are
                              never shown on the page.
                          </small>

                          @php
                              $storyGallery = old('story_gallery', $page->story_gallery ?? []);
                          @endphp

                          @for ($i = 0; $i < ($storyImageSlots ?? 10); $i++)
                              @php $storyImage = $storyGallery[$i] ?? []; @endphp
                              <div class="card mb-3 shadow-sm">
                                  <div class="card-body">
                                      <div class="row g-3 align-items-start">
                                          <div class="col-md-3">
                                              <label class="form-label small">Image</label>
                                              <x-media-picker
                                                  name="story_gallery[{{ $i }}][image_id]"
                                                  :selected="$storyImage['image_id'] ?? null"
                                                  label="Select Image"
                                              />
                                          </div>
                                          <div class="col-md-9">
                                              <label class="form-label small">Caption</label>
                                              <input type="text" name="story_gallery[{{ $i }}][caption]" class="form-control"
                                                     placeholder="e.g. Our team at Kilimanjaro base camp" value="{{ $storyImage['caption'] ?? '' }}">
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          @endfor
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Why Choose Us Subtitle</label>
                      <div class="col-sm-9">
                          <input type="text" name="why_choose_subtitle" class="form-control" 
                                 value="{{ old('why_choose_subtitle', $page->why_choose_subtitle) }}">
                      </div>
                  </div>

                  <!-- Stats Counters -->
                  <div class="row mb-3 mt-4">
                      <label class="col-sm-3 col-form-label fw-bold">Stats Counters</label>
                      <div class="col-sm-9">
                          <div id="stats-counters-repeater">
                              @php
                                  $statsCounters = old('stats_counters', $page->stats_counters ?? []);
                              @endphp
                              @forelse($statsCounters as $index => $counter)
                                  <div class="counter-item row g-2 mb-2 align-items-center">
                                      <div class="col-3">
                                          <input type="text" name="stats_counters[{{ $index }}][value]" class="form-control"
                                                 placeholder="e.g. 50" value="{{ $counter['value'] ?? '' }}">
                                      </div>
                                      <div class="col-2">
                                          <input type="text" name="stats_counters[{{ $index }}][suffix]" class="form-control"
                                                 placeholder="+" value="{{ $counter['suffix'] ?? '+' }}">
                                      </div>
                                      <div class="col-5">
                                          <input type="text" name="stats_counters[{{ $index }}][label]" class="form-control"
                                                 placeholder="e.g. Destinations" value="{{ $counter['label'] ?? '' }}">
                                      </div>
                                      <div class="col-2">
                                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                                      </div>
                                  </div>
                              @empty
                                  <div class="counter-item row g-2 mb-2 align-items-center">
                                      <div class="col-3">
                                          <input type="text" name="stats_counters[0][value]" class="form-control" placeholder="e.g. 50">
                                      </div>
                                      <div class="col-2">
                                          <input type="text" name="stats_counters[0][suffix]" class="form-control" placeholder="+" value="+">
                                      </div>
                                      <div class="col-5">
                                          <input type="text" name="stats_counters[0][label]" class="form-control" placeholder="e.g. Destinations">
                                      </div>
                                      <div class="col-2">
                                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                                      </div>
                                  </div>
                              @endforelse
                          </div>
                          <button type="button" id="add-counter" class="btn btn-sm btn-outline-primary mt-1">
                              <i class="bi bi-plus-circle"></i> Add Counter
                          </button>
                          <small class="text-muted d-block mt-1">e.g. "50+ Destinations", "7000+ Happy Travellers".</small>
                      </div>
                  </div>

                  <!-- Team Members -->
                  <div class="row mb-3 mt-4">
                      <label class="col-sm-3 col-form-label fw-bold">Team Members</label>
                      <div class="col-sm-9">
                          <small class="text-muted d-block mb-3">
                              Up to {{ $teamMemberSlots ?? 10 }} slots below. Leave a slot's name blank to skip it —
                              blank slots are never shown on the page.
                          </small>

                          @php
                              $teamMembers = old('team_members', $page->custom_data ?? []);
                          @endphp

                          @for ($i = 0; $i < ($teamMemberSlots ?? 10); $i++)
                              @php $member = $teamMembers[$i] ?? []; @endphp
                              <div class="card mb-3 shadow-sm">
                                  <div class="card-body">
                                      <div class="row g-3 align-items-start">
                                          <div class="col-md-3">
                                              <label class="form-label small">Photo</label>
                                              <x-media-picker
                                                  name="team_members[{{ $i }}][photo_image_id]"
                                                  :selected="$member['photo_image_id'] ?? null"
                                                  label="Select Photo"
                                              />
                                          </div>
                                          <div class="col-md-4">
                                              <label class="form-label small">Name</label>
                                              <input type="text" name="team_members[{{ $i }}][name]" class="form-control"
                                                     value="{{ $member['name'] ?? '' }}">
                                          </div>
                                          <div class="col-md-5">
                                              <label class="form-label small">Role</label>
                                              <input type="text" name="team_members[{{ $i }}][role]" class="form-control"
                                                     placeholder="e.g. Founder & Lead Guide" value="{{ $member['role'] ?? '' }}">
                                          </div>
                                          <div class="col-12">
                                              <label class="form-label small">Short Bio (optional)</label>
                                              <textarea name="team_members[{{ $i }}][bio]" class="form-control" rows="2">{{ $member['bio'] ?? '' }}</textarea>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          @endfor
                      </div>
                  </div>
              @endif

              <!-- General extra fields (usable on any page) -->
              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Extra Heading (optional)</label>
                  <div class="col-sm-9">
                      <input type="text" name="extra_heading" class="form-control" 
                             value="{{ old('extra_heading', $page->extra_heading) }}">
                  </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Extra Subheading (optional)</label>
                  <div class="col-sm-9">
                      <textarea name="extra_subheading" class="form-control" rows="3">{{ old('extra_subheading', $page->extra_subheading) }}</textarea>
                  </div>
              </div>

              <!-- CTA (call to action) -->
              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">CTA Button Text</label>
                  <div class="col-sm-9">
                      <input type="text" name="cta_text" class="form-control" 
                             value="{{ old('cta_text', $page->cta_text) }}">
                  </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">CTA Button Link</label>
                  <div class="col-sm-9">
                      <input type="url" name="cta_link" class="form-control" 
                             value="{{ old('cta_link', $page->cta_link) }}">
                  </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="status" class="form-select">
                    <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Published</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control" 
                         value="{{ old('order', $page->order) }}">
                  <small class="text-muted">Lower number = appears higher in lists/menus</small>
                </div>
              </div>

              <div class="d-flex justify-content-end mt-4"><button type="button" class="btn btn-primary page-step-next">Next: SEO Settings <i class="bi bi-arrow-right"></i></button></div>
              </div>

              <div class="page-step-panel d-none" data-page-step="2">
              <h5 class="card-title mt-2 mb-3">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" 
                         value="{{ old('meta_title', $page->meta_title) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3">
                    {{ old('meta_description', $page->meta_description) }}
                  </textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" 
                         value="{{ old('meta_keywords', $page->meta_keywords) }}">
                  <small class="text-muted">comma separated, e.g. safari, kilimanjaro, tanzania tours</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_edit" name="no_robots" value="1" {{ old('no_robots', $page->no_robots) ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_robots_edit">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this page is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-4">
              <button type="button" class="btn btn-outline-secondary page-step-back"><i class="bi bi-arrow-left"></i> Back</button>
              <div class="d-flex gap-2 mb-3">
                  <button type="submit" class="btn btn-primary">Update Page</button>
                  <a href="{{ $backUrl }}" class="btn btn-secondary ms-2">Cancel</a>
              </div>
              </div>
            </form>
</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
      $(document).ready(function () {
          let counterIndex = $('#stats-counters-repeater .counter-item').length;

          $('#add-counter').on('click', function () {
              const newCounter = `
                  <div class="counter-item row g-2 mb-2 align-items-center">
                      <div class="col-3">
                          <input type="text" name="stats_counters[${counterIndex}][value]" class="form-control" placeholder="e.g. 50">
                      </div>
                      <div class="col-2">
                          <input type="text" name="stats_counters[${counterIndex}][suffix]" class="form-control" placeholder="+" value="+">
                      </div>
                      <div class="col-5">
                          <input type="text" name="stats_counters[${counterIndex}][label]" class="form-control" placeholder="e.g. Destinations">
                      </div>
                      <div class="col-2">
                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                      </div>
                  </div>`;
              $('#stats-counters-repeater').append(newCounter);
              counterIndex++;
          });

          $(document).on('click', '.remove-counter', function () {
              $(this).closest('.counter-item').remove();
          });
      });
  </script>
  @include('admin.pages.partials.wizard-script', ['autosaveEnabled' => !$editingSiteInfo])
@endsection
