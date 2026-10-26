@foreach ($categories as $category)
  <option value="{{ $category->id }}" @selected((string) ($selectedCategoryId ?? '') === (string) $category->id)>{{ $category->full_path }}</option>
  @include('admin.media.partials.category-options', ['categories' => $category->children, 'selectedCategoryId' => $selectedCategoryId ?? null])
@endforeach
