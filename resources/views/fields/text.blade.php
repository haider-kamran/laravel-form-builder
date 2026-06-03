<input type="text" 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    class="form-control @error($field->name) is-invalid @enderror" 
    value="{{ old($field->name) }}"
    {{ $field->is_required ? 'required' : '' }}>
