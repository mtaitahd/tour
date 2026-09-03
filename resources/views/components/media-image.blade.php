@if ($fallbackUrl)
  <picture>
    @if ($srcset)
      <source type="image/webp" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
    @endif
    <img
      src="{{ $fallbackUrl }}"
      alt="{{ $alt }}"
      @if ($class) class="{{ $class }}" @endif
      loading="{{ $loading }}"
      decoding="async"
    >
  </picture>
@else
  {{-- No image at all (not even a placeholder configured) — render nothing rather
       than a broken <img> tag with an empty src. --}}
@endif
