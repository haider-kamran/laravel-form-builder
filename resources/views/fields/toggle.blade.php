@php
    $checked = old($field->name, $field->options['default'] ?? false);
@endphp

<div class="form-check form-switch">
    <input class="form-check-input @error($field->name) is-invalid @enderror"
        type="checkbox"
        role="switch"
        name="{{ $field->name }}"
        id="field-{{ $field->name }}"
        value="1"
        @if($checked) checked @endif
        {{ $field->is_required ? 'required' : '' }}>
    <label class="form-check-label" for="field-{{ $field->name }}">
        {{ $field->options['toggle_label'] ?? 'Yes / No' }}
    </label>
</div>
