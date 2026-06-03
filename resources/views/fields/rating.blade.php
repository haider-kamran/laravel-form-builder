@php
    $maxStars = $field->options['max'] ?? 5;
    $currentRating = old($field->name, $field->options['default'] ?? 0);
@endphp

<div class="rating-field" id="rating-wrapper-{{ $field->name }}">
    <input type="hidden"
        name="{{ $field->name }}"
        id="field-{{ $field->name }}"
        value="{{ $currentRating }}"
        {{ $field->is_required ? 'required' : '' }}>

    <div class="d-inline-flex flex-row-reverse gap-1" role="radiogroup" aria-label="{{ $field->label }}">
        @for($i = $maxStars; $i >= 1; $i--)
            <button
                type="button"
                class="btn-star @if($i <= $currentRating) active @endif"
                data-value="{{ $i }}"
                data-target="field-{{ $field->name }}"
                aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                style="background:none;border:none;cursor:pointer;font-size:1.8rem;color:{{ $i <= $currentRating ? '#f5c518' : '#ccc' }};padding:0 2px;transition:color .15s ease;">
                ★
            </button>
        @endfor
    </div>

    <div class="text-muted small mt-1" id="rating-label-{{ $field->name }}">
        @if($currentRating > 0)
            {{ $currentRating }} / {{ $maxStars }} stars selected
        @else
            No rating selected
        @endif
    </div>
</div>

<script>
    (function () {
        const wrapper = document.getElementById('rating-wrapper-{{ $field->name }}');
        if (!wrapper) return;
        const stars    = wrapper.querySelectorAll('.btn-star');
        const hidden   = document.getElementById('field-{{ $field->name }}');
        const labelEl  = document.getElementById('rating-label-{{ $field->name }}');
        const maxStars = {{ $maxStars }};

        const paint = (value) => {
            stars.forEach(btn => {
                const v = parseInt(btn.dataset.value);
                btn.style.color = v <= value ? '#f5c518' : '#ccc';
            });
        };

        stars.forEach(btn => {
            btn.addEventListener('click', () => {
                const value = parseInt(btn.dataset.value);
                hidden.value = value;
                paint(value);
                labelEl.textContent = value + ' / ' + maxStars + ' stars selected';
            });

            btn.addEventListener('mouseenter', () => paint(parseInt(btn.dataset.value)));
            btn.addEventListener('mouseleave', () => paint(parseInt(hidden.value) || 0));
        });
    })();
</script>
