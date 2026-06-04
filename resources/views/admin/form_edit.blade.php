@extends('form-builder::admin.layout')
@section('page-title', $form->exists ? 'Edit Form' : 'New Form')

@push('styles')
<style>
/* ── BUILDER LAYOUT ─────────────────────────────────── */
.builder-wrap {
    display: grid;
    grid-template-columns: 220px 1fr 280px;
    gap: 20px;
    height: calc(100vh - 120px);
    overflow: hidden;
}

/* Left — Field Palette */
.field-palette {
    background: var(--fb-surface);
    border: 1px solid var(--fb-border);
    border-radius: var(--fb-radius);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
.palette-group-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--fb-muted);
    padding: 12px 14px 4px;
    font-weight: 700;
}
.palette-item {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 14px;
    cursor: grab;
    border-radius: 8px;
    margin: 1px 6px;
    font-size: 12px; font-weight: 500;
    color: var(--fb-text);
    transition: background .12s;
    user-select: none;
}
.palette-item:hover { background: var(--fb-accent-soft); color: var(--fb-accent); }
.palette-item i { font-size: 15px; width: 18px; text-align: center; }
.palette-item .pro-badge {
    margin-left: auto; font-size: 9px; font-weight: 700;
    background: var(--fb-accent); color: #fff; padding: 1px 6px; border-radius: 50px;
}

/* Centre — Canvas */
.builder-canvas-wrap {
    display: flex; flex-direction: column;
    background: var(--fb-surface);
    border: 1px solid var(--fb-border);
    border-radius: var(--fb-radius);
    overflow: hidden;
}
.canvas-toolbar {
    border-bottom: 1px solid var(--fb-border);
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.canvas-toolbar span { font-weight: 600; font-size: 13px; }
#field-canvas {
    flex: 1; overflow-y: auto; padding: 14px;
    min-height: 300px;
}
.canvas-empty {
    height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: var(--fb-muted); gap: 10px;
}
.canvas-empty i { font-size: 2.5rem; opacity: .25; }
.canvas-empty p { font-size: 13px; }

/* Field card on canvas */
.field-card {
    background: var(--fb-surface-2);
    border: 1px solid var(--fb-border);
    border-radius: 10px;
    margin-bottom: 10px;
    transition: border-color .15s, box-shadow .15s;
    cursor: pointer;
}
.field-card:hover { border-color: var(--fb-accent); }
.field-card.selected { border-color: var(--fb-accent); box-shadow: 0 0 0 2px var(--fb-accent-soft); }
.field-card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px;
}
.field-card-header .drag-handle { cursor: grab; color: var(--fb-muted); font-size: 14px; }
.field-card-header .field-icon { color: var(--fb-accent); font-size: 15px; }
.field-card-header .field-info { flex: 1; min-width: 0; }
.field-card-header .field-label-display { font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.field-card-header .field-type-display { font-size: 10px; text-transform: uppercase; letter-spacing: .6px; color: var(--fb-muted); }
.field-card-actions { display: flex; align-items: center; gap: 6px; margin-left: auto; }
.field-card-actions button {
    background: none; border: none; color: var(--fb-muted); cursor: pointer;
    padding: 4px 6px; border-radius: 6px; font-size: 14px; transition: all .12s;
}
.field-card-actions button:hover { background: rgba(239,68,68,.12); color: var(--fb-danger); }
.field-card-actions .req-badge {
    font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 50px;
    background: rgba(245,158,11,.12); color: var(--fb-warning); cursor: default;
}

/* Right — Properties Panel */
.props-panel {
    background: var(--fb-surface);
    border: 1px solid var(--fb-border);
    border-radius: var(--fb-radius);
    overflow-y: auto;
    display: flex; flex-direction: column;
}
.props-panel-header {
    padding: 14px 16px;
    border-bottom: 1px solid var(--fb-border);
    font-weight: 700; font-size: 13px;
    display: flex; align-items: center; gap: 8px;
}
.props-panel-body { padding: 14px; flex: 1; overflow-y: auto; }
.props-empty { color: var(--fb-muted); font-size: 13px; text-align: center; padding: 40px 20px; }

/* Form meta panel */
.meta-section {
    background: var(--fb-surface);
    border: 1px solid var(--fb-border);
    border-radius: var(--fb-radius);
    padding: 18px;
    margin-bottom: 20px;
}

/* SortableJS ghost & chosen */
.sortable-ghost { opacity: .35; border: 2px dashed var(--fb-accent) !important; }
.sortable-chosen { box-shadow: 0 8px 24px rgba(108,99,255,.3); }

/* Tab nav */
.fb-tabs { display: flex; border-bottom: 1px solid var(--fb-border); margin-bottom: 16px; }
.fb-tab {
    padding: 8px 14px; cursor: pointer; font-size: 12px; font-weight: 600;
    color: var(--fb-muted); border-bottom: 2px solid transparent; transition: all .15s;
}
.fb-tab.active { color: var(--fb-accent); border-bottom-color: var(--fb-accent); }

/* Settings Tab */
.settings-field { margin-bottom: 14px; }
.notification-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--fb-accent-soft); color: var(--fb-accent);
    border-radius: 50px; padding: 3px 10px; font-size: 12px;
    margin: 3px;
}
.notification-chip button { background:none;border:none;color:var(--fb-accent);cursor:pointer;font-size:14px;line-height:1;padding:0 0 0 4px; }
</style>
@endpush

@section('topbar-actions')
    <a href="{{ route('form-builder.admin.index') }}" class="btn-fb-ghost">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    @if($form->exists)
        <a href="{{ route('form-builder.admin.submissions', $form->id) }}" class="btn-fb-ghost">
            <i class="bi bi-table"></i> Submissions
        </a>
    @endif
    <button type="button" id="btn-save-form" class="btn-fb-primary">
        <i class="bi bi-floppy"></i> Save Form
    </button>
@endsection

@section('content')

{{-- Hidden form --}}
<form id="main-form" method="POST"
    action="{{ $form->exists ? route('form-builder.admin.update', $form->id) : route('form-builder.admin.store') }}">
    @csrf
    @if($form->exists) @method('PUT') @endif
    <input type="hidden" name="fields"   id="fields-json">
    <input type="hidden" name="settings" id="settings-json">
</form>

{{-- FORM META --}}
<div class="meta-section mb-3">
    <div class="row g-3">
        <div class="col-md-5">
            <label class="fb-label">Form Title <span style="color:var(--fb-danger)">*</span></label>
            <input type="text" id="meta-title" class="fb-input" value="{{ old('title', $form->title) }}" placeholder="e.g. Contact Us" required>
        </div>
        <div class="col-md-3">
            <label class="fb-label">Slug</label>
            <input type="text" id="meta-slug" class="fb-input" value="{{ old('slug', $form->slug) }}" placeholder="auto-generated">
        </div>
        <div class="col-md-4">
            <label class="fb-label">Description</label>
            <input type="text" id="meta-description" class="fb-input" value="{{ old('description', $form->description) }}" placeholder="Short description (optional)">
        </div>
        <div class="col-auto d-flex align-items-end">
            <div class="form-check form-switch mb-0" style="margin-top:8px">
                <input class="form-check-input" type="checkbox" id="meta-active" {{ old('is_active', $form->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="meta-active" style="color:var(--fb-text);font-size:13px">Active</label>
            </div>
        </div>
    </div>
</div>

{{-- BUILDER --}}
<div class="builder-wrap">

    {{-- LEFT: PALETTE --}}
    <div class="field-palette" id="field-palette">
        @foreach(\Hyderkamran\FormBuilder\Enums\FieldType::grouped() as $group => $types)
            <div class="palette-group-title">{{ $group }}</div>
            @foreach($types as $type)
                <div class="palette-item"
                    draggable="true"
                    data-type="{{ $type->value }}"
                    data-label="{{ $type->label() }}"
                    data-icon="{{ $type->icon() }}">
                    <i class="bi {{ $type->icon() }}"></i>
                    {{ $type->label() }}
                    @if($type->group() === 'Pro')
                        <span class="pro-badge">PRO</span>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    {{-- CENTRE: CANVAS --}}
    <div class="builder-canvas-wrap">
        <div class="canvas-toolbar">
            <span><i class="bi bi-kanban me-1"></i> Drop fields here to build</span>
            <small class="text-muted-fb">Drag to reorder</small>
        </div>
        <div id="field-canvas">
            <div class="canvas-empty" id="canvas-empty">
                <i class="bi bi-arrow-left-circle"></i>
                <p>Drag a field from the palette to get started</p>
            </div>
        </div>
    </div>

    {{-- RIGHT: PROPERTIES PANEL --}}
    <div class="props-panel" id="props-panel">
        <div class="props-panel-header">
            <i class="bi bi-sliders2"></i>
            <span id="props-panel-title">Properties</span>
        </div>
        <div class="props-panel-body">
            {{-- Field Properties (shown when field selected) --}}
            <div id="field-props" style="display:none">
                <div class="fb-tabs">
                    <div class="fb-tab active" data-tab="basic">Basic</div>
                    <div class="fb-tab" data-tab="validation">Validation</div>
                    <div class="fb-tab" data-tab="options">Options</div>
                    <div class="fb-tab" data-tab="logic">Logic</div>
                </div>

                {{-- BASIC --}}
                <div id="tab-basic">
                    <div class="settings-field">
                        <label class="fb-label">Label</label>
                        <input type="text" id="prop-label" class="fb-input" placeholder="Field label">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Field Name (key)</label>
                        <input type="text" id="prop-name" class="fb-input" placeholder="e.g. email_address">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Placeholder</label>
                        <input type="text" id="prop-placeholder" class="fb-input" placeholder="">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Help Text</label>
                        <input type="text" id="prop-help" class="fb-input" placeholder="">
                    </div>
                    <div class="settings-field d-flex align-items-center justify-content-between">
                        <label class="fb-label mb-0">Required</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="prop-required">
                        </div>
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Column Width</label>
                        <select id="prop-width" class="fb-select">
                            <option value="12">100% (Full Row)</option>
                            <option value="9">75% (3/4 Row)</option>
                            <option value="8">66% (2/3 Row)</option>
                            <option value="6">50% (Half Row)</option>
                            <option value="4">33% (1/3 Row)</option>
                            <option value="3">25% (1/4 Row)</option>
                        </select>
                    </div>
                </div>

                {{-- VALIDATION --}}
                <div id="tab-validation" style="display:none">
                    <div class="settings-field">
                        <label class="fb-label">Min Length / Value</label>
                        <input type="number" id="prop-min" class="fb-input" placeholder="">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Max Length / Value</label>
                        <input type="number" id="prop-max" class="fb-input" placeholder="">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Regex Pattern</label>
                        <input type="text" id="prop-regex" class="fb-input" placeholder="e.g. ^[A-Z]+$">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Custom Rule (Laravel)</label>
                        <input type="text" id="prop-custom-rule" class="fb-input" placeholder="e.g. unique:users,email">
                    </div>
                </div>

                {{-- OPTIONS (choices for select/radio/checkbox) --}}
                <div id="tab-options" style="display:none">
                    <div id="choices-editor">
                        <label class="fb-label">Choices</label>
                        <small class="d-block text-muted-fb mb-2">One per line: <code style="color:var(--fb-accent)">value|Label</code></small>
                        <textarea id="prop-choices" class="fb-textarea" rows="6" placeholder="pakistan|Pakistan&#10;usa|United States"></textarea>
                    </div>
                    <div id="range-options" style="display:none">
                        <div class="settings-field">
                            <label class="fb-label">Min</label>
                            <input type="number" id="prop-range-min" class="fb-input" value="0">
                        </div>
                        <div class="settings-field">
                            <label class="fb-label">Max</label>
                            <input type="number" id="prop-range-max" class="fb-input" value="100">
                        </div>
                        <div class="settings-field">
                            <label class="fb-label">Step</label>
                            <input type="number" id="prop-range-step" class="fb-input" value="1">
                        </div>
                    </div>
                    <div id="rating-options" style="display:none">
                        <div class="settings-field">
                            <label class="fb-label">Max Stars</label>
                            <input type="number" id="prop-rating-max" class="fb-input" value="5" min="3" max="10">
                        </div>
                    </div>
                    <div id="file-options" style="display:none">
                        <div class="settings-field d-flex align-items-center justify-content-between">
                            <label class="fb-label mb-0">Allow Multiple</label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="prop-multiple">
                            </div>
                        </div>
                        <div class="settings-field">
                            <label class="fb-label">Accepted Types (MIME)</label>
                            <input type="text" id="prop-accept" class="fb-input" placeholder="image/*,application/pdf">
                        </div>
                    </div>
                </div>

                {{-- LOGIC --}}
                <div id="tab-logic" style="display:none">
                    <p class="text-muted-fb" style="font-size:12px;margin-bottom:12px">Show or hide this field based on another field's value.</p>
                    <div class="settings-field">
                        <label class="fb-label">Watch Field (name)</label>
                        <input type="text" id="prop-logic-field" class="fb-input" placeholder="e.g. country">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Operator</label>
                        <select id="prop-logic-operator" class="fb-select">
                            <option value="=">=</option>
                            <option value="!=">≠</option>
                            <option value=">">&gt;</option>
                            <option value="<">&lt;</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Value</label>
                        <input type="text" id="prop-logic-value" class="fb-input" placeholder="e.g. Pakistan">
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Then</label>
                        <select id="prop-logic-action" class="fb-select">
                            <option value="show">Show this field</option>
                            <option value="hide">Hide this field</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Form Settings (shown when no field is selected) --}}
            <div id="form-settings">
                <div class="fb-tabs">
                    <div class="fb-tab active" data-tab="notif">Notifications</div>
                    <div class="fb-tab" data-tab="webhook">Webhook</div>
                </div>
                <div id="stab-notif">
                    <div class="settings-field d-flex align-items-center justify-content-between">
                        <label class="fb-label mb-0">Enable email alerts</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="s-enable-notif" {{ ($form->settings['enable_notifications'] ?? true) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Recipients</label>
                        <div id="recipient-chips" class="mb-2"></div>
                        <input type="text" id="s-recipients-input" class="fb-input" placeholder="email@example.com → Enter">
                        <input type="hidden" id="s-recipients-hidden" value="{{ implode(',', (array)($form->settings['notification_recipients'] ?? [])) }}">
                    </div>
                    <button type="button" id="btn-test-email" class="btn-fb-ghost w-100 justify-content-center" style="margin-top:4px">
                        <i class="bi bi-envelope"></i> Send Test Email
                    </button>
                </div>
                <div id="stab-webhook" style="display:none">
                    <div class="settings-field d-flex align-items-center justify-content-between">
                        <label class="fb-label mb-0">Enable Webhook</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="s-enable-webhook" {{ ($form->settings['webhook_enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label class="fb-label">Webhook URL</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="s-webhook-url" class="fb-input" value="{{ $form->settings['webhook_url'] ?? '' }}" placeholder="https://...">
                        </div>
                    </div>
                    <button type="button" id="btn-test-webhook" class="btn-fb-ghost w-100 justify-content-center" style="margin-top:4px">
                        <i class="bi bi-lightning"></i> Test Webhook
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /.builder-wrap --}}

@endsection

@push('scripts')
@php
    $existingFields = $form->fields ? $form->fields->map(fn($f) => [
        'id'     => $f->id,
        'label'  => $f->label,
        'name'   => $f->name,
        'type'   => $f->type,
        'required' => $f->is_required,
        'options'  => $f->options ?? [],
        'validation_rules' => $f->validation_rules ?? [],
    ]) : [];
@endphp
<script>
    const EXISTING_FIELDS = @json($existingFields);
    const FORM_ID   = {{ $form->exists ? $form->id : 'null' }};
    const ADMIN_URLS = {
        testWebhook : '{{ $form->exists ? route('form-builder.admin.test-webhook', $form->id) : '' }}',
        testEmail   : '{{ $form->exists ? route('form-builder.admin.test-email', $form->id) : '' }}',
        store       : '{{ route('form-builder.admin.store') }}',
    };
</script>
<script src="{{ asset('vendor/form-builder/admin.js') }}"></script>
@endpush
