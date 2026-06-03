<div class="d-flex align-items-center gap-3">
    <input type="color"
        name="{{ $field->name }}"
        id="field-{{ $field->name }}"
        class="form-control form-control-color @error($field->name) is-invalid @enderror"
        value="{{ old($field->name, $field->options['default'] ?? '#563d7c') }}"
        title="Choose a color"
        {{ $field->is_required ? 'required' : '' }}>
    <span class="text-muted small" id="color-preview-{{ $field->name }}">
        {{ old($field->name, $field->options['default'] ?? '#563d7c') }}
    </span>
</div>
<script>
    (function () {
        const input = document.getElementById('field-{{ $field->name }}');
        const preview = document.getElementById('color-preview-{{ $field->name }}');
        if (input && preview) {
            input.addEventListener('input', () => preview.textContent = input.value);
        }
    })();
</script>
