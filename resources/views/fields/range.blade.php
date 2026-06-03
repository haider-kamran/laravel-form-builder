@php
    $min   = $field->options['min'] ?? 0;
    $max   = $field->options['max'] ?? 100;
    $step  = $field->options['step'] ?? 1;
    $current = old($field->name, $field->options['default'] ?? $min);
@endphp

<div class="d-flex align-items-center gap-3">
    <input type="range"
        name="{{ $field->name }}"
        id="field-{{ $field->name }}"
        class="form-range @error($field->name) is-invalid @enderror"
        min="{{ $min }}"
        max="{{ $max }}"
        step="{{ $step }}"
        value="{{ $current }}"
        oninput="document.getElementById('range-output-{{ $field->name }}').textContent = this.value"
        {{ $field->is_required ? 'required' : '' }}>
    <output id="range-output-{{ $field->name }}" class="badge bg-secondary">{{ $current }}</output>
</div>
<div class="d-flex justify-content-between text-muted small mt-1">
    <span>{{ $min }}</span>
    <span>{{ $max }}</span>
</div>
