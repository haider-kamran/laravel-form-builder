<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $form->title }} — New Submission</title>
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; color: #1e293b; margin: 0; padding: 0; background: #f8fafc; }
    .email-wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .email-header { background: linear-gradient(135deg, #6c63ff 0%, #4f46e5 100%); padding: 30px 32px; color: #fff; }
    .email-header h1 { margin: 0 0 4px; font-size: 20px; font-weight: 700; }
    .email-header p { margin: 0; opacity: .8; font-size: 13px; }
    .email-body { padding: 28px 32px; }
    .field-row { border-bottom: 1px solid #f1f5f9; padding: 10px 0; display: flex; gap: 16px; }
    .field-row:last-child { border-bottom: none; }
    .field-label { min-width: 140px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; padding-top: 2px; }
    .field-value { flex: 1; color: #1e293b; word-break: break-word; }
    .email-footer { background: #f8fafc; padding: 16px 32px; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    .meta-pill { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; margin-right: 6px; }
</style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-header">
        <h1>📋 New Form Submission</h1>
        <p>{{ $form->title }}</p>
    </div>
    <div class="email-body">
        <p style="margin:0 0 20px;color:#475569;font-size:13px">
            A new submission was received at <strong>{{ $submission->created_at->format('d M Y, H:i') }}</strong>.
            <span class="meta-pill">Form: {{ $form->slug }}</span>
        </p>

        <div class="submission-fields">
            @foreach($form->fields as $field)
                @php
                    $typeValue = $field->type instanceof \Hyderkamran\FormBuilder\Enums\FieldType
                        ? $field->type->value : (string) $field->type;
                    $value = $submission->data[$field->name] ?? null;
                    $display = is_array($value) ? implode(', ', array_filter((array)$value, 'is_scalar')) : (string)($value ?? '');
                @endphp
                @if($typeValue !== 'hidden')
                    <div class="field-row">
                        <div class="field-label">{{ $field->label }}</div>
                        <div class="field-value">{{ $display ?: '—' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    <div class="email-footer">
        Sent by <strong>{{ config('app.name') }}</strong> · Laravel Form Builder
        · Submission ID #{{ $submission->id ?? 'test' }}
    </div>
</div>
</body>
</html>
