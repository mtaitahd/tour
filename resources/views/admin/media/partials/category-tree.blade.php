{{--
    Recursive category tree, rendered as a nested filter list. Used on the Media
    Library index page's sidebar. Expects $categories (a Collection of root
    MediaCategory models with children eager-loaded — see
    MediaLibraryService::categoryTree()) and $activeCategoryId (nullable int, the
    currently-selected filter, if any).
--}}
@foreach ($categories as $category)
    <li data-category-node data-category-name="{{ $category->name }}">
      @if ($category->children->isNotEmpty())
        <details data-category-branch>
          <summary class="small py-1">{{ $category->name }}</summary>
          <div class="ms-2">
      @endif
        <a href="{{ route('admin.media.index', array_merge(request()->query(), ['category_id' => $category->id, 'page' => null])) }}"
           data-category-link="{{ $category->id }}"
           class="d-flex justify-content-between align-items-center text-decoration-none px-2 py-1 rounded small {{ (int) $activeCategoryId === $category->id ? 'bg-primary text-white' : 'text-body' }}">
            <span>{{ $category->children->isNotEmpty() ? 'All in ' . $category->name : $category->name }}</span>
            <span title="Images assigned directly to this category" class="badge {{ (int) $activeCategoryId === $category->id ? 'bg-white text-primary' : 'bg-light text-muted' }}">{{ $category->media_count }}</span>
        </a>

        @if ($category->children->isNotEmpty())
            <ul class="list-unstyled ms-3 mt-1">
                @include('admin.media.partials.category-tree', ['categories' => $category->children, 'activeCategoryId' => $activeCategoryId])
            </ul>
        @endif
      @if ($category->children->isNotEmpty())
          </div>
        </details>
      @endif
    </li>
@endforeach
