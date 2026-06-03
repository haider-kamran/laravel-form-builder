<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $form->title }} Submissions</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        h1, p { margin: 0; }
        .meta { margin-top: 1rem; color: #555; }
    </style>
</head>
<body>
    <h1>{{ $form->title }}</h1>
    @if($form->description)
        <p>{{ $form->description }}</p>
    @endif
    <p class="meta">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Submission ID</th>
                <th>Submitted At</th>
                @foreach($form->fields as $field)
                    <th>{{ ucfirst(str_replace(['-', '_'], ' ', $field->name)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($form->submissions as $submission)
                <tr>
                    <td>{{ $submission->id }}</td>
                    <td>{{ $submission->created_at->format('Y-m-d H:i:s') }}</td>
                    @foreach($form->fields as $field)
                        <td>{{ is_array($submission->data[$field->name] ?? null) ? json_encode($submission->data[$field->name]) : ($submission->data[$field->name] ?? '') }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
