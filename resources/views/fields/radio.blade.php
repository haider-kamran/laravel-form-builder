@if(isset($field->options['choices']) && is_array($field->options['choices']))
    @foreach($field->options['choices'] as $value => $label)
        <div class="form-check">
            <input class="form-check-input @error($field->name) is-invalid @enderror" 
                type="radio" 
                name="{{ $field->name }}" 
                id="field-{{ $field->name }}-{{ $loop->index }}" 
                value="{{ $value }}"
                {{ old($field->name) == $value ? 'checked' : '' }}
                {{ $field->is_required ? 'required' : '' }}>
            <label class="form-check-label" for="field-{{ $field->name }}-{{ $loop->index }}">
                {{ $label }}
            </label>
        </div>
    @endforeach
@endif
