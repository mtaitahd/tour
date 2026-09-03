{{--
    Country card used by the "Worldwide Group Travellers" section.
    Renders one flag + name + chevron card. Expects:
      $wwgtC            => ['code' => 'US', 'name' => 'United States']
      $wwgtFeaturedFlag => bool  (true = one of the first 24 shown by default)
--}}
<a href="{{ route('tours.index') }}"
   class="wwgt-country{{ $wwgtFeaturedFlag ? '' : ' wwgt-country--more' }}"
   data-name="{{ \Illuminate\Support\Str::lower($wwgtC['name']) }}"
   data-raw="{{ $wwgtC['name'] }}"
   data-code="{{ $wwgtC['code'] }}"
   {{ $wwgtFeaturedFlag ? '' : 'hidden' }}
   role="listitem">
    {{-- TODO(country-page): when per-country travel pages (or a country tour-filter)
         routes exist, point this link to the relevant route (e.g. route('country.show', $wwgtC['code']))
         or a tour filter query. Until then it safely points to the live tours index so the
         link is never broken. --}}
    <span class="wwgt-country__flag" aria-hidden="true">
        <img src="https://flagcdn.com/w40/{{ \Illuminate\Support\Str::lower($wwgtC['code']) }}.png"
             srcset="https://flagcdn.com/w80/{{ \Illuminate\Support\Str::lower($wwgtC['code']) }}.png 2x"
             alt="{{ $wwgtC['name'] }} flag"
             width="40" height="30" loading="lazy">
    </span>
    <span class="wwgt-country__name">{{ $wwgtC['name'] }}</span>
    <span class="wwgt-country__chev" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </span>
</a>
