{{--
    Recursive row renderer for the category management index. $depth controls
    indentation so nesting is visually obvious without needing a JS tree widget.
--}}
@foreach ($categories as $category)
    <li class="d-flex justify-content-between align-items-center py-2 border-bottom" style="padding-left: {{ $depth * 24 }}px;">
        <div>
            @if ($depth > 0)
                <i class="bi bi-arrow-return-right text-muted me-1"></i>
            @endif
            <span class="fw-medium">{{ $category->name }}</span>
            <span class="badge bg-light text-muted ms-2">{{ $category->media_count }} image{{ $category->media_count === 1 ? '' : 's' }}</span>
        </div>
        <div>
            <a href="{{ route('admin.media.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.media.categories.destroy', $category) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete &quot;{{ $category->name }}&quot;{{ $category->children->isNotEmpty() ? ' and its ' . $category->children->count() . ' sub-categor' . ($category->children->count() === 1 ? 'y' : 'ies') : '' }}? Images in it will not be deleted, just unassigned.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </li>

    @if ($category->children->isNotEmpty())
        @include('admin.media.categories.partials.tree-row', ['categories' => $category->children, 'depth' => $depth + 1])
    @endif
@endforeach
