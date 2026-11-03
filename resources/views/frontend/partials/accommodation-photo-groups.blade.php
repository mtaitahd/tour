@forelse($groups as $stay)
    <section class="td-acc-photo-group" aria-label="{{ $stay['name'] }} photos">
        <h4 class="td-acc-photo-group__title">{{ $stay['name'] }}</h4>
        <p class="td-acc-photo-group__type">{{ $stay['type'] }} · {{ count($stay['photos']) }} photos</p>
        <div class="td-acc-thumbs">
            @foreach($stay['photos'] as $index => $photo)
                <button type="button" class="td-thumb td-js-lightbox" data-group="{{ $stay['group'] }}" data-index="{{ $index }}" aria-label="Open {{ $stay['name'] }} photo {{ $index + 1 }}">
                    <img src="{{ $photo['u'] }}" alt="{{ $photo['c'] }}" loading="lazy">
                </button>
            @endforeach
        </div>
        <button type="button" class="td-morelink td-js-lightbox" data-group="{{ $stay['group'] }}" data-index="0">View all {{ count($stay['photos']) }} photos</button>
    </section>
@empty
    <span class="td-acc-nophotos">—</span>
@endforelse
