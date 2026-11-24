<div class="border rounded p-3 mb-2" data-row>
  @foreach($field['columns'] as $column)
    <label class="form-label d-block">{{ ucwords(str_replace('_', ' ', $column)) }}
      @if(str_ends_with($column, '_image_id'))
        <x-media-picker name="settings[{{ $key }}][{{ $rowIndex }}][{{ $column }}]" :selected="$row[$column] ?? null" label="Select Image" />
      @else
        <textarea class="form-control" rows="2" name="settings[{{ $key }}][{{ $rowIndex }}][{{ $column }}]">{{ $row[$column] ?? '' }}</textarea>
      @endif
    </label>
  @endforeach
  <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Remove Item</button>
</div>
