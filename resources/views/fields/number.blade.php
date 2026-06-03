<input type="number" 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    class="form-control @error($field->name) is-invalid @enderror" 
    value="{{ old($field->name) }}"
    @if(isset($field->validation_rules['min'])) min="{{ $field->validation_rules['min'] }}" @endif
    @if(isset($field->validation_rules['max'])) max="{{ $field->validation_rules['max'] }}" @endif
    {{ $field->is_required ? 'required' : '' }}>
