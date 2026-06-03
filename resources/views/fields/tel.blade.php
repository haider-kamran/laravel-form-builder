<input type="tel"
    name="{{ $field->name }}"
    id="field-{{ $field->name }}"
    class="form-control @error($field->name) is-invalid @enderror"
    value="{{ old($field->name) }}"
    placeholder="{{ $field->options['placeholder'] ?? '+1 (555) 000-0000' }}"
    @if(isset($field->options['pattern'])) pattern="{{ $field->options['pattern'] }}" @endif
    {{ $field->is_required ? 'required' : '' }}>
