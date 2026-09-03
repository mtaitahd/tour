{{--
    Recursive <option> renderer for the parent-category select. $excludeId (optional)
    prevents a category from being offered as its own parent when editing.
--}}
@foreach ($categories as $category)
    @continue(isset($excludeId) && $category->id === $excludeId)

    <option value="{{ $category->id }}" {{ (string) $selectedId === (string) $category->id ? 'selected' : '' }}>
        {{ str_repeat('— ', $depth) }}{{ $category->name }}
    </option>

    @if ($category->children->isNotEmpty())
        @include('admin.media.categories.partials.parent-options', [
            'categories' => $category->children,
            'depth' => $depth + 1,
            'selectedId' => $selectedId,
            'excludeId' => $excludeId ?? null,
        ])
    @endif
@endforeach
