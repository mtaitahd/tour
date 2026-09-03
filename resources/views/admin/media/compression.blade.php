@extends('admin.layouts.app')
@section('title', 'Compress Images')

@push('styles')
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
@endpush

@section('content')
  <div class="pagetitle">
    <h1>Compress Images</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item active">Compress Images</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="row gy-4">

      {{-- Compression settings --}}
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

            <form method="POST" action="{{ route('admin.media.compression.run') }}">
              @csrf

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
                  @foreach ($categories as $category)
                    <option value="{{ $category['collection'] }}">{{ str_replace('_', ' ', ucwords($category['collection'])) }}
                      ({{ $category['models']->sum('count') }})</option>
                  @endforeach
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
              <strong>{{ number_format($totalImages) }}</strong>
            </div>
            <p class="text-muted small mb-0">
              Compression is applied to the WebP variants on disk. Category rows below show how many
              images live in each collection.
            </p>
          </div>
        </div>
      </div>

      {{-- Categories summary --}}
      <div class="col-lg-7">
        <div class="card compress-card">
          <div class="card-body">
            <h5 class="card-title">Images by category</h5>
            <p class="text-muted small mb-3">
              Images are arranged by their collection — <strong>gallery</strong> stays in gallery,
              <strong>hero / safari_car / itinerary</strong> belong to tours &amp; destinations, etc.
            </p>

            @if ($categories->isEmpty())
              <p class="text-muted text-center py-4">No images found in the library.</p>
            @else
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
                    @foreach ($categories as $category)
                      @php
                        $totalCount = $category['models']->sum('count');
                        $adminCount = $category['models']->sum('admin_uploaded');
                        $totalSize = $category['models']->sum('size_bytes');
                      @endphp
                      <tr class="collection-row">
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <span class="csv-icon bg-primary bg-opacity-10 text-primary">
                              <i class="bi bi-folder2-open"></i>
                            </span>
                            <div>
                              <div class="fw-medium">{{ str_replace('_', ' ', ucwords($category['collection'])) }}</div>
                              <small class="text-muted">{{ $category['collection'] }}</small>
                            </div>
                          </div>
                        </td>
                        <td>
                          @forelse ($category['models'] as $model)
                            <span class="badge bg-light text-dark me-1"
                                  title="{{ $model['label'] }}">{{ $model['label'] }}</span>
                          @empty
                            <span class="text-muted small">—</span>
                          @endforelse
                        </td>
                        <td class="text-end fw-medium">{{ $totalCount }}</td>
                        <td class="text-end">
                          <span class="badge {{ $adminCount === $totalCount ? 'bg-success' : 'bg-light text-dark' }}">
                            <i class="bi bi-person-check me-1"></i>{{ $adminCount }} / {{ $totalCount }}
                          </span>
                        </td>
                        <td class="text-end">
                          {{ $totalSize >= 1048576 ? number_format($totalSize / 1048576, 1) . ' MB' : number_format($totalSize / 1024, 1) . ' KB' }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>

        @if (session('compression_result'))
          @php $cr = session('compression_result'); @endphp
          <div class="card compress-card mt-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                  <i class="bi bi-terminal me-1"></i> Compression results
                </h5>
                <span class="badge bg-light text-dark">
                  {{ $cr['mode'] === 'filesize' ? 'Target ' . $cr['value'] . ' KB' : 'Quality ' . $cr['value'] . '%' }}
                </span>
              </div>

              <div class="row text-center mb-3">
                <div class="col-4">
                  <div class="text-muted small">Before</div>
                  <strong>{{ $cr['origTotal'] >= 1048576 ? number_format($cr['origTotal']/1048576,2) . ' MB' : number_format($cr['origTotal']/1024,1) . ' KB' }}</strong>
                </div>
                <div class="col-4">
                  <div class="text-muted small">After</div>
                  <strong>{{ $cr['newTotal'] >= 1048576 ? number_format($cr['newTotal']/1048576,2) . ' MB' : number_format($cr['newTotal']/1024,1) . ' KB' }}</strong>
                </div>
                <div class="col-4">
                  <div class="text-muted small">Saved</div>
                  <strong class="text-success">{{ $cr['saved'] >= 1048576 ? number_format($cr['saved']/1048576,2) . ' MB' : number_format($cr['saved']/1024,1) . ' KB' }}</strong>
                </div>
              </div>

              <div class="result-log">
                @foreach ($cr['results'] as $res)
                  <div>
                    <span class="ok">OK</span>
                    <span class="dim">#{{ $res['media_id'] }} [{{ $res['collection'] }}] {{ $res['name'] }}</span>
                    <span class="muted">({{ round($res['orig']/1024,1) }}KB &rarr; {{ round($res['new']/1024,1) }}KB)</span>
                  </div>
                @endforeach
                <div class="mt-2">
                  <span class="ok font-weight-bold">Done!</span>
                  <span class="dim"> {{ $cr['shrunk'] }} of {{ count($cr['results']) }} image(s) shrank, total saved {{ $cr['saved']/1024 >= 1024 ? number_format($cr['saved']/1048576,2) . ' MB' : number_format($cr['saved']/1024,1) . ' KB' }}.</span>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>

    </div>
  </section>
@endsection

@push('scripts')
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
@endpush
