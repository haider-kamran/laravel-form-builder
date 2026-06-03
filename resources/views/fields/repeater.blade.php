@php
    $fieldName = $field->name;
    $subFields = $field->options['fields'] ?? [];
    $rows      = [];
    $oldRaw    = old($fieldName);

    if (is_string($oldRaw)) {
        $rows = json_decode($oldRaw, true) ?: [];
    } elseif (is_array($oldRaw)) {
        $rows = $oldRaw;
    }

    if (empty($rows)) {
        $rows = [$field->options['default'] ?? []];
    }
@endphp

<div class="repeater-wrapper" 
    data-repeater-name="{{ $fieldName }}" 
    data-repeater-fields="{{ e(json_encode($subFields)) }}">

    {{-- Existing rows (server-side rendered for old() support) --}}
    <div class="repeater-items">
        @foreach($rows as $index => $row)
            <div class="repeater-item card mb-3" data-index="{{ $index }}">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <strong class="small text-muted">Row {{ $index + 1 }}</strong>
                    @if($index > 0)
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-repeater">
                            &times; Remove
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($subFields as $subField)
                            @php
                                $subName  = "{$fieldName}[{$index}][{$subField['name']}]";
                                $subValue = $row[$subField['name']] ?? '';
                                $subType  = $subField['type'] ?? 'text';
                            @endphp
                            <div class="col-md-{{ $subField['width'] ?? 6 }} mb-1">
                                <label class="form-label small fw-semibold">
                                    {{ $subField['label'] ?? ucfirst($subField['name']) }}
                                    @if(!empty($subField['required'])) <span class="text-danger">*</span> @endif
                                </label>

                                @if($subType === 'textarea')
                                    <textarea name="{{ $subName }}" class="form-control form-control-sm repeater-input" data-field-name="{{ $subField['name'] }}" rows="2" {{ !empty($subField['required']) ? 'required' : '' }}>{{ $subValue }}</textarea>
                                @elseif($subType === 'select')
                                    <select name="{{ $subName }}" class="form-select form-select-sm repeater-input" data-field-name="{{ $subField['name'] }}" {{ !empty($subField['required']) ? 'required' : '' }}>
                                        <option value="">Select…</option>
                                        @foreach($subField['choices'] ?? [] as $choiceVal => $choiceLabel)
                                            <option value="{{ $choiceVal }}" {{ $subValue == $choiceVal ? 'selected' : '' }}>{{ $choiceLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $subType }}" name="{{ $subName }}" class="form-control form-control-sm repeater-input" data-field-name="{{ $subField['name'] }}" value="{{ $subValue }}" {{ !empty($subField['required']) ? 'required' : '' }}>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary btn-add-repeater mt-1">
        + Add Row
    </button>
</div>
