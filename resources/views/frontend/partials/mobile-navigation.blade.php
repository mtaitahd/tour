<ul class="av-drawer__list">
    @foreach($parentLabels as $key => $fallback)
        @php
            $mobileMenu = $megaMenu[$key] ?? [];
            $mobileLabel = $mobileMenu['label'] ?? $fallback['label'];
            $mobileUrl = $mobileMenu['trigger_url'] ?? $fallback['url'];
            $mobileItems = $mobileMenu['categories'] ?? [];
        @endphp
        <li @class(['has-dropdown' => !empty($mobileItems)])>
            @if(!empty($mobileItems))
                <div class="av-drawer__row">
                    <a href="{{ $mobileUrl }}">{{ $mobileLabel }}</a>
                    <button class="av-drawer__toggle" type="button" aria-expanded="false"
                            aria-controls="avDrawerSub-{{ $key }}" aria-label="Toggle {{ $mobileLabel }} submenu">
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                </div>
                <ul class="av-drawer__sub" id="avDrawerSub-{{ $key }}">
                    @foreach($mobileItems as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
                    @endforeach
                    <li><a href="{{ $mobileUrl }}">View all {{ $mobileLabel }}</a></li>
                </ul>
            @else
                <a class="av-drawer__link" href="{{ $mobileUrl }}">{{ $mobileLabel }}</a>
            @endif
        </li>
    @endforeach
</ul>
