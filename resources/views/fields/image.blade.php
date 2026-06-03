<input type="file" 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    accept="image/*"
    class="form-control @error($field->name) is-invalid @enderror" 
    {{ $field->is_required ? 'required' : '' }}
    @if(isset($field->options['multiple']) && $field->options['multiple']) multiple name="{{ $field->name }}[]" @endif>
