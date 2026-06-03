<input type="url"
    name="{{ $field->name }}"
    id="field-{{ $field->name }}"
    class="form-control @error($field->name) is-invalid @enderror"
    value="{{ old($field->name) }}"
    placeholder="{{ $field->options['placeholder'] ?? 'https://' }}"
    {{ $field->is_required ? 'required' : '' }}>
