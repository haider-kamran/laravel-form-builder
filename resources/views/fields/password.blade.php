<input type="password" 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    class="form-control @error($field->name) is-invalid @enderror" 
    {{ $field->is_required ? 'required' : '' }}>
