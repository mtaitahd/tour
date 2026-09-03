
<?php $__env->startSection('title', 'Create Blog Post'); ?>
<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Create New Blog Post</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.blog-posts.index')); ?>">Blog Posts</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div>
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Post Details</h5>
            <?php if($errors->any()): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.blog-posts.store')); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>
              <!-- Title & Slug -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" required value="<?php echo e(old('title')); ?>">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug</label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug')); ?>">
                  <small class="text-muted">Leave empty to auto-generate from title</small>
                </div>
              </div>
              <!-- Category -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Category</label>
                <div class="col-sm-10">
                  <select name="category_id" class="form-select">
                    <option value="">-- Select Category --</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                        <?php echo e($category->name); ?>

                      </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>
              <!-- Featured Image -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured Image</label>
                <div class="col-sm-10">
                  <div class="mb-3">
                    <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'featured_image_id','selected' => old('featured_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
                    <small class="text-muted d-block mt-1">Choose an existing image from the library, or upload a new one below.</small>
                  </div>
                  <input type="file" name="featured_image" accept="image/*" class="form-control">
                  <small class="text-muted">Or upload a new image directly. Recommended: 1200&times;800 px, max 5MB</small>
                </div>
              </div>
              <!-- Content - Quill (only this part changed) -->
              <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Content <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <div class="quill-editor border rounded" style="height: 420px;"></div>
                  <input type="hidden" name="content" class="quill-hidden-input" value="<?php echo e(old('content')); ?>">
                </div>
              </div>
              <!-- Status & Publish Date -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-5">
                  <select name="status" class="form-select">
                    <option value="draft" <?php echo e(old('status', 'draft') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="published" <?php echo e(old('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                  </select>
                </div>
                <div class="col-sm-5">
                  <label class="form-label">Publish Date (optional)</label>
                  <input type="date" name="published_at" class="form-control" value="<?php echo e(old('published_at')); ?>">
                </div>
              </div>
              <!-- SEO Fields -->
              <h5 class="mt-5 mb-3">SEO Settings</h5>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" value="<?php echo e(old('meta_title')); ?>">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3"><?php echo e(old('meta_description')); ?></textarea>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" value="<?php echo e(old('meta_keywords')); ?>">
                  <small class="text-muted">comma separated</small>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_create" name="no_robots" value="1" <?php echo e(old('no_robots') ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="no_robots_create">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this post is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>
              <!-- Featured Post -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo e(old('is_featured') ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show as featured post</label>
                  </div>
                </div>
              </div>
              <!-- Submit -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Create Blog Post</button>
                  <a href="<?php echo e(route('admin.blog-posts.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quill Editor (only added) -->
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

  <!-- Font families (Times New Roman + popular website fonts) -->
  <style>
    .ql-font-arial { font-family: Arial, sans-serif !important; }
    .ql-font-helvetica { font-family: Helvetica, Arial, sans-serif !important; }
    .ql-font-times-new-roman { font-family: "Times New Roman", serif !important; }
    .ql-font-georgia { font-family: Georgia, serif !important; }
    .ql-font-verdana { font-family: Verdana, sans-serif !important; }
    .ql-font-courier-new { font-family: "Courier New", monospace !important; }
  </style>

  <script>
    $(document).ready(function () {
        function initQuill(editorDiv) {
            if (!editorDiv.length || editorDiv.hasClass('ql-container')) return;

            const quill = new Quill(editorDiv[0], {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'font': ['arial', 'helvetica', 'times-new-roman', 'georgia', 'verdana', 'courier-new'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],   <!-- All 6 headings -->
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            const hiddenInput = editorDiv.siblings('.quill-hidden-input').first();
            if (hiddenInput.val()) {
                quill.root.innerHTML = hiddenInput.val();
            }
            quill.on('text-change', function () {
                hiddenInput.val(quill.root.innerHTML);
            });
        }

        // Initialize Quill for the blog content
        $('.quill-editor').each(function () {
            initQuill($(this));
        });
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\blog-posts\create.blade.php ENDPATH**/ ?>