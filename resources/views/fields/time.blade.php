<input type="time"
    name="{{ $field->name }}"
    id="field-{{ $field->name }}"
    class="form-control @error($field->name) is-invalid @enderror"
    value="{{ old($field->name) }}"
    @if(isset($field->options['min'])) min="{{ $field->options['min'] }}" @endif
    @if(isset($field->options['max'])) max="{{ $field->options['max'] }}" @endif
    {{ $field->is_required ? 'required' : '' }}>
