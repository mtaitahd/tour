<?php $__env->startSection('title', 'Compress Images'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .compress-card { border: 1px solid #f0f0f0; border-radius: .6rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .csv-icon { width: 44px; height: 44px; border-radius: .5rem; display: grid; place-items: center; }
    .result-log { background: #1a1a2e; color: #7dffb0; font-family: 'Consolas','Courier New',monospace; font-size: .78rem;
                  padding: 1rem; border-radius: .5rem; max-height: 420px; overflow-y: auto; line-height: 1.65; }
    .result-log .muted { color: #ffd47e; }
    .result-log .dim { color: #9aa0b5; }
    .result-log .ok { color: #7dffb0; }
    .collection-row:hover { background: #f8f9fa; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Compress Images</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item active">Compress Images</li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i> <?php echo e(session('error')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <section class="section">
    <div class="row gy-4">

      
      <div class="col-lg-5">
        <div class="card compress-card">
          <div class="card-body">
            <h5 class="card-title">Compression Settings</h5>
            <p class="text-muted small mb-4">
              Re-encode the <strong>WebP versions</strong> (thumb / medium / large) that Spatie
              Media Library generates for every upload. The original image you uploaded is never
              touched — only the smaller variants served to visitors are compressed. You can choose
              either a fixed quality or a target file size.
            </p>

            <form method="POST" action="<?php echo e(route('admin.media.compression.run')); ?>">
              <?php echo csrf_field(); ?>

              <div class="mb-3">
                <label class="form-label fw-semibold">Compression mode</label>
                <div class="d-flex flex-column gap-2">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="mode" id="modeQuality" value="quality" checked
                           onchange="toggleMode()">
                    <label class="form-check-label" for="modeQuality">
                      <i class="bi bi-sliders me-1"></i> Fixed quality
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="mode" id="modeFilesize" value="filesize"
                           onchange="toggleMode()">
                    <label class="form-check-label" for="modeFilesize">
                      <i class="bi bi-file-earmark-arrow-down me-1"></i> Target file size
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-3" id="qualityWrap">
                <label class="form-label" for="quality">Quality (%)</label>
                <input type="number" id="quality" name="quality_value" class="form-control" value="70"
                       min="25" max="95" required>
                <div class="form-text">Lower = smaller file, lower quality. 70 is a good balance.</div>
              </div>

              <div class="mb-3 d-none" id="filesizeWrap">
                <label class="form-label" for="filesize">Target file size (KB)</label>
                <div class="input-group">
                  <input type="number" id="filesize" name="filesize_value" class="form-control" value="10"
                         min="1" max="10240">
                  <span class="input-group-text">KB</span>
                </div>
                <div class="form-text">Keeps lowering quality until each variant is at or below this size (quality floor 25%).</div>
              </div>

              <input type="hidden" name="value" id="compressionValue">

              <div class="mb-3">
                <label class="form-label fw-semibold">Images to compress</label>
                <select name="collection" id="collectionSelect" class="form-select">
                  <option value="all">All categories</option>
                  <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category['collection']); ?>"><?php echo e(str_replace('_', ' ', ucwords($category['collection']))); ?>

                      (<?php echo e($category['models']->sum('count')); ?>)</option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>

              <button type="submit" class="btn btn-primary w-100" id="compressSubmitBtn">
                <i class="bi bi-compress me-1"></i> Compress Images
              </button>
            </form>
          </div>
        </div>

        <div class="card compress-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">Library overview</h5>
            </div>
            <div class="d-flex justify-content-between small mb-2">
              <span>Total images</span>
              <strong><?php echo e(number_format($totalImages)); ?></strong>
            </div>
            <p class="text-muted small mb-0">
              Compression is applied to the WebP variants on disk. Category rows below show how many
              images live in each collection.
            </p>
          </div>
        </div>
      </div>

      
      <div class="col-lg-7">
        <div class="card compress-card">
          <div class="card-body">
            <h5 class="card-title">Images by category</h5>
            <p class="text-muted small mb-3">
              Images are arranged by their collection — <strong>gallery</strong> stays in gallery,
              <strong>hero / safari_car / itinerary</strong> belong to tours &amp; destinations, etc.
            </p>

            <?php if($categories->isEmpty()): ?>
              <p class="text-muted text-center py-4">No images found in the library.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr class="text-uppercase small text-muted">
                      <th style="min-width:200px">Collection</th>
                      <th>Used by</th>
                      <th class="text-end">Images</th>
                      <th class="text-end">Admin uploaded</th>
                      <th class="text-end">Current size</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <?php
                        $totalCount = $category['models']->sum('count');
                        $adminCount = $category['models']->sum('admin_uploaded');
                        $totalSize = $category['models']->sum('size_bytes');
                      ?>
                      <tr class="collection-row">
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <span class="csv-icon bg-primary bg-opacity-10 text-primary">
                              <i class="bi bi-folder2-open"></i>
                            </span>
                            <div>
                              <div class="fw-medium"><?php echo e(str_replace('_', ' ', ucwords($category['collection']))); ?></div>
                              <small class="text-muted"><?php echo e($category['collection']); ?></small>
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php $__empty_1 = true; $__currentLoopData = $category['models']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-light text-dark me-1"
                                  title="<?php echo e($model['label']); ?>"><?php echo e($model['label']); ?></span>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted small">—</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-end fw-medium"><?php echo e($totalCount); ?></td>
                        <td class="text-end">
                          <span class="badge <?php echo e($adminCount === $totalCount ? 'bg-success' : 'bg-light text-dark'); ?>">
                            <i class="bi bi-person-check me-1"></i><?php echo e($adminCount); ?> / <?php echo e($totalCount); ?>

                          </span>
                        </td>
                        <td class="text-end">
                          <?php echo e($totalSize >= 1048576 ? number_format($totalSize / 1048576, 1) . ' MB' : number_format($totalSize / 1024, 1) . ' KB'); ?>

                        </td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if(session('compression_result')): ?>
          <?php $cr = session('compression_result'); ?>
          <div class="card compress-card mt-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                  <i class="bi bi-terminal me-1"></i> Compression results
                </h5>
                <span class="badge bg-light text-dark">
                  <?php echo e($cr['mode'] === 'filesize' ? 'Target ' . $cr['value'] . ' KB' : 'Quality ' . $cr['value'] . '%'); ?>

                </span>
              </div>

              <div class="row text-center mb-3">
                <div class="col-4">
                  <div class="text-muted small">Before</div>
                  <strong><?php echo e($cr['origTotal'] >= 1048576 ? number_format($cr['origTotal']/1048576,2) . ' MB' : number_format($cr['origTotal']/1024,1) . ' KB'); ?></strong>
                </div>
                <div class="col-4">
                  <div class="text-muted small">After</div>
                  <strong><?php echo e($cr['newTotal'] >= 1048576 ? number_format($cr['newTotal']/1048576,2) . ' MB' : number_format($cr['newTotal']/1024,1) . ' KB'); ?></strong>
                </div>
                <div class="col-4">
                  <div class="text-muted small">Saved</div>
                  <strong class="text-success"><?php echo e($cr['saved'] >= 1048576 ? number_format($cr['saved']/1048576,2) . ' MB' : number_format($cr['saved']/1024,1) . ' KB'); ?></strong>
                </div>
              </div>

              <div class="result-log">
                <?php $__currentLoopData = $cr['results']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div>
                    <span class="ok">OK</span>
                    <span class="dim">#<?php echo e($res['media_id']); ?> [<?php echo e($res['collection']); ?>] <?php echo e($res['name']); ?></span>
                    <span class="muted">(<?php echo e(round($res['orig']/1024,1)); ?>KB &rarr; <?php echo e(round($res['new']/1024,1)); ?>KB)</span>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="mt-2">
                  <span class="ok font-weight-bold">Done!</span>
                  <span class="dim"> <?php echo e($cr['shrunk']); ?> of <?php echo e(count($cr['results'])); ?> image(s) shrank, total saved <?php echo e($cr['saved']/1024 >= 1024 ? number_format($cr['saved']/1048576,2) . ' MB' : number_format($cr['saved']/1024,1) . ' KB'); ?>.</span>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleMode() {
    const isFilesize = document.getElementById('modeFilesize').checked;
    document.getElementById('qualityWrap').classList.toggle('d-none', isFilesize);
    document.getElementById('filesizeWrap').classList.toggle('d-none', !isFilesize);
    document.getElementById('quality').required = !isFilesize;
    document.getElementById('filesize').required = isFilesize;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('compressSubmitBtn').addEventListener('click', function () {
        const isFilesize = document.getElementById('modeFilesize').checked;
        document.getElementById('compressionValue').value = isFilesize
            ? document.getElementById('filesize').value
            : document.getElementById('quality').value;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Compressing…';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\compression.blade.php ENDPATH**/ ?>