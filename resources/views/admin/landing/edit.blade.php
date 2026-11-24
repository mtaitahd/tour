@extends('admin.layouts.app')
@section('title', 'Landing Page — '.$editor['title'])
@section('content')
  <div class="pagetitle"><h1>Landing Page</h1></div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())
    <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif
  <div class="row">
    <nav class="col-lg-3 mb-3" aria-label="Homepage sections">
      <div class="list-group">
        @foreach($sections as $slug => $item)
          <a class="list-group-item list-group-item-action {{ $slug === $section ? 'active' : '' }}"
             href="{{ route('admin.landing.edit', $slug) }}" @if($slug === $section) aria-current="page" @endif>{{ $item['title'] }}</a>
        @endforeach
      </div>
      <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline-primary mt-3">Preview Homepage</a>
    </nav>
    <div class="col-lg-9">
      <div class="card"><div class="card-body pt-3">
        <h2 class="h4 mb-3">{{ $editor['title'] }}</h2>
        @if(isset($editor['manage']) && Route::has($editor['manage']))
          <a href="{{ route($editor['manage']) }}" class="btn btn-outline-primary mb-3">Manage {{ $editor['title'] }} Content</a>
        @endif
        <form method="POST" action="{{ route('admin.landing.update', $section) }}">
          @csrf
          @foreach($editor['fields'] as $key => $field)
            @php($value = old("settings.$key", \App\Models\Setting::get($key, $field['default'] ?? '')))
            @if(($field['blank_uses_default'] ?? false) && !$value && !session()->hasOldInput("settings.$key"))
              @php($value = $field['default'])
            @endif
            <div class="mb-4">
              <label class="form-label fw-semibold" for="landing-{{ $key }}">{{ $field['label'] }}</label>
              @if($field['type'] === 'image')
                <x-media-picker name="settings[{{ $key }}]" :selected="$value" :label="'Select '.$field['label']" />
              @elseif($field['type'] === 'checkbox')
                <input type="checkbox" id="landing-{{ $key }}" name="settings[{{ $key }}]" value="1" @checked($value)>
              @elseif($field['type'] === 'list')
                @php($rows = old("settings.$key", \App\Models\Setting::json($key, $field['rows'] ?? \App\Support\LandingDefaults::rows($field['defaults'] ?? $key))))
                <div data-landing-list data-next-index="{{ count($rows) }}">
                  <div data-rows>
                    @foreach($rows as $index => $row)
                      @include('admin.landing.row', ['rowIndex' => $index])
                    @endforeach
                  </div>
                  <template>@include('admin.landing.row', ['rowIndex' => '__ROW__', 'row' => []])</template>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-add-row>Add Item</button>
                </div>
              @else
                <textarea class="form-control" rows="3" id="landing-{{ $key }}" name="settings[{{ $key }}]">{{ $value }}</textarea>
              @endif
            </div>
          @endforeach
          <button class="btn btn-primary" type="submit">Save {{ $editor['title'] }}</button>
        </form>
      </div></div>
    </div>
  </div>
@endsection
@push('scripts')
<script>
document.addEventListener('click', event => {
    const add = event.target.closest('[data-add-row]');
    if (add) {
        const list = add.closest('[data-landing-list]');
        const index = Number(list.dataset.nextIndex);
        list.dataset.nextIndex = index + 1;
        const holder = document.createElement('div');
        holder.innerHTML = list.querySelector('template').innerHTML.replaceAll('__ROW__', index);
        list.querySelector('[data-rows]').append(...holder.childNodes);
    }
    const remove = event.target.closest('[data-remove-row]');
    if (remove) remove.closest('[data-row]').remove();
});
</script>
@endpush
