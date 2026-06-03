<input type="hidden" 
    name="{{ $field->name }}" 
    id="field-{{ $field->name }}" 
    value="{{ old($field->name, $field->options['default'] ?? '') }}">
