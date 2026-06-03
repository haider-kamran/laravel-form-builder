<select 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    class="form-select @error($field->name) is-invalid @enderror"
    {{ $field->is_required ? 'required' : '' }}>
    
    <option value="">Select an option</option>
    @if(isset($field->options['choices']) && is_array($field->options['choices']))
        @foreach($field->options['choices'] as $value => $label)
            <option value="{{ $value }}" {{ old($field->name) == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    @endif
</select>
