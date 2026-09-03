{{--
    Recursive nested checkboxes for assigning an image to one or more categories.
    Expects $categories (Collection of MediaCategory with children eager-loaded) and
    $selectedIds (array of currently-selected category ids).
--}}
@foreach ($categories as $category)
    <div class="form-check">
        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
               class="form-check-input" id="cat-{{ $category->id }}"
               {{ in_array($category->id, $selectedIds) ? 'checked' : '' }}>
        <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}</label>
    </div>

    @if ($category->children->isNotEmpty())
        <div class="ms-4">
            @include('admin.media.partials.category-checkboxes', ['categories' => $category->children, 'selectedIds' => $selectedIds])
        </div>
    @endif
@endforeach
