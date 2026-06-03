<textarea 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    class="form-control @error($field->name) is-invalid @enderror" 
    rows="{{ $field->options['rows'] ?? 3 }}"
    {{ $field->is_required ? 'required' : '' }}>{{ old($field->name) }}</textarea>
