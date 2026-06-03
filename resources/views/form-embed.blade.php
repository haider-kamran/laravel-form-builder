<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: system-ui, sans-serif; padding: 20px; }
        .form-builder-embed { max-width: 640px; margin: 0 auto; }
        .form-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 6px; }
        .form-desc  { color: #6c757d; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="form-builder-embed">
    @if(session('form_success'))
        <div class="alert alert-success">{{ session('form_success') }}</div>
    @endif

    <div class="form-title">{{ $form->title }}</div>
    @if($form->description)
        <div class="form-desc">{{ $form->description }}</div>
    @endif

    {!! \Hyderkamran\FormBuilder\Facades\FormBuilder::render($form->slug) !!}
</div>
<script src="{{ asset('vendor/form-builder/form-builder.js') }}"></script>
</body>
</html>
