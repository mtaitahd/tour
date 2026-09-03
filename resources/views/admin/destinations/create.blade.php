@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Create Destination</h1>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.destinations.store') }}">
          @csrf

          <div class="row my-3">
            <label class="col-sm-2 col-form-label">Name *</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Slug</label>
            <div class="col-sm-10">
              <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
              <small>Auto-generated if empty</small>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Country Code *</label>
            <div class="col-sm-10">
              <select name="country_code" class="form-select" required>
                <option value="TZ">Tanzania (TZ)</option>
                <option value="KE">Kenya (KE)</option>
                <option value="UG">Uganda (UG)</option>
                <option value="RW">Rwanda (RW)</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Type</label>
            <div class="col-sm-10">
              <input type="text" name="type" class="form-control" value="{{ old('type') }}" placeholder="national_park, beach, mountain...">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Description</label>
            <div class="col-sm-10">
              <textarea name="description" class="form-control tinymce-editor" rows="8">{{ old('description') }}</textarea>
            </div>
          </div>

          <!-- SEO fields -->
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Meta Title</label>
            <div class="col-sm-10">
              <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Meta Description</label>
            <div class="col-sm-10">
              <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') }}</textarea>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Meta Keywords</label>
            <div class="col-sm-10">
              <input type="text" name="meta_keywords" class="form-control" 
                     value="{{ old('meta_keywords') }}">
              <small>comma separated</small>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Featured</label>
            <div class="col-sm-10">
              <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
            </div>
          </div>

          <!-- FAQ Section -->
          <div class="row mb-5 mt-5">
            <label class="col-sm-2 col-form-label"><strong>FAQs</strong></label>
            <div class="col-sm-10">
              <div id="faqs-wrapper">
                <div class="faq-item row mb-3 align-items-start">
                  <div class="col-md-5">
                    <input type="text" name="faqs[0][question]" class="form-control" placeholder="Question">
                  </div>
                  <div class="col-md-5">
                    <textarea name="faqs[0][answer]" class="form-control" rows="3" placeholder="Answer"></textarea>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
                  </div>
                </div>
              </div>
              <button type="button" id="add-faq" class="btn btn-sm btn-primary mt-2">Add FAQ</button>
            </div>
          </div>

          <!-- Reviews -->
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Reviews</label>
            <div class="col-sm-10">
              <textarea name="reviews_embed" class="form-control" rows="4"
                        placeholder="Paste a review page URL, or a review widget embed code (e.g. Elfsight)">{{ old('reviews_embed') }}</textarea>
              <small class="text-muted">A plain link renders as a "Read Reviews" button. An embed snippet renders as-is.</small>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Create Destination</button>
        </form>
      </div>
    </div>
  </section>

  <script>
    $(document).ready(function () {
      let faqIndex = $('#faqs-wrapper .faq-item').length;

      $('#add-faq').on('click', function () {
        const newFaq = `
          <div class="faq-item row mb-3 align-items-start">
            <div class="col-md-5">
              <input type="text" name="faqs[${faqIndex}][question]" class="form-control" placeholder="Question">
            </div>
            <div class="col-md-5">
              <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3" placeholder="Answer"></textarea>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
            </div>
          </div>`;
        $('#faqs-wrapper').append(newFaq);
        faqIndex++;
      });

      $(document).on('click', '.remove-faq', function () {
        $(this).closest('.faq-item').remove();
      });
    });
  </script>
@endsection