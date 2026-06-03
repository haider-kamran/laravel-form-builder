<div class="form-builder-wrapper">
    @if(session('form_success'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
            {{ session('form_success') }}
        </div>
    @endif

    <form action="{{ route('form-builder.submit', $form->slug) }}"
          method="POST"
          enctype="multipart/form-data"
          novalidate>
        @csrf

        @if($form->title)
            <h4 class="mb-1 fw-bold">{{ $form->title }}</h4>
        @endif
        @if($form->description)
            <p class="text-muted mb-3" style="font-size:.9rem">{{ $form->description }}</p>
        @endif

        <div class="form-fields">
            @foreach($form->fields as $field)
                @php
                    $typeValue = $field->type instanceof \Hyderkamran\FormBuilder\Enums\FieldType
                        ? $field->type->value
                        : (string) $field->type;

                    $logic = $field->options['logic'] ?? null;
                @endphp

                @if($typeValue === 'hidden')
                    @includeIf("form-builder::fields.hidden", ['field' => $field])
                @else
                    <div class="mb-3 field-wrapper"
                         data-field-name="{{ $field->name }}"
                         @if($logic) data-logic="{{ e(json_encode($logic)) }}" @endif>

                        @if($typeValue !== 'checkbox' && $typeValue !== 'toggle' && $typeValue !== 'rating')
                            <label for="field-{{ $field->name }}" class="form-label fw-semibold">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-danger" aria-hidden="true">*</span>
                                @endif
                            </label>
                        @endif

                        @includeIf("form-builder::fields.{$typeValue}", ['field' => $field])

                        @if($field->options['help'] ?? null)
                            <div class="form-text text-muted">{{ $field->options['help'] }}</div>
                        @endif

                        @error($field->name)
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary mt-2">
            {{ $form->settings['submit_label'] ?? 'Submit' }}
        </button>
    </form>
</div>

@pushOnce('form-builder-scripts')
    <script src="{{ asset('vendor/form-builder/form-builder.js') }}"></script>
@endPushOnce
