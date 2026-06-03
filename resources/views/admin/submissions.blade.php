@extends('form-builder::admin.layout')
@section('page-title', 'Submissions — ' . $form->title)

@push('styles')
<style>
.submissions-table-wrap { overflow-x: auto; }
.data-cell { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.data-cell[title] { cursor: help; }
.export-bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
</style>
@endpush

@section('topbar-actions')
    <a href="{{ route('form-builder.admin.edit', $form->id) }}" class="btn-fb-ghost">
        <i class="bi bi-pencil"></i> Edit Form
    </a>
    <div class="export-bar">
        <a href="{{ route('form-builder.admin.export', ['id' => $form->id, 'format' => 'csv']) }}" class="btn-fb-ghost">
            <i class="bi bi-filetype-csv"></i> CSV
        </a>
        <a href="{{ route('form-builder.admin.export', ['id' => $form->id, 'format' => 'xlsx']) }}" class="btn-fb-ghost">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <a href="{{ route('form-builder.admin.export', ['id' => $form->id, 'format' => 'pdf']) }}" class="btn-fb-ghost">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>
@endsection

@section('content')

{{-- STATS --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(108,99,255,.15);color:#6c63ff"><i class="bi bi-send"></i></div>
            <div><div class="fb-stat-label">Total Submissions</div><div class="fb-stat-value">{{ $form->submissions->count() }}</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(34,197,94,.15);color:#22c55e"><i class="bi bi-layout-text-window-reverse"></i></div>
            <div><div class="fb-stat-label">Form Fields</div><div class="fb-stat-value">{{ $form->fields->count() }}</div></div>
        </div>
    </div>
    @if($form->submissions->count())
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(245,158,11,.15);color:#f59e0b"><i class="bi bi-clock-history"></i></div>
            <div><div class="fb-stat-label">Last Submission</div>
                <div style="font-size:14px;font-weight:600;margin-top:4px">{{ $form->submissions->sortByDesc('created_at')->first()?->created_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- TABLE --}}
<div class="fb-card">
    <div class="fb-card-header">
        <span><i class="bi bi-table me-2"></i>Submission Data</span>
        <span class="text-muted-fb" style="font-size:12px">{{ $form->submissions->count() }} total</span>
    </div>
    <div class="fb-card-body submissions-table-wrap" style="padding:0">
        @if($form->submissions->isEmpty())
            <div class="text-center py-5 text-muted-fb">
                <i class="bi bi-inbox" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.3"></i>
                <p>No submissions yet for this form.</p>
            </div>
        @else
            <table class="fb-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Submitted At</th>
                        <th>IP Address</th>
                        @foreach($form->fields as $field)
                            <th>{{ $field->label }}</th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($form->submissions->sortByDesc('created_at') as $submission)
                        <tr>
                            <td class="text-muted-fb">{{ $submission->id }}</td>
                            <td style="white-space:nowrap">
                                {{ $submission->created_at->format('d M Y') }}
                                <div style="font-size:11px;color:var(--fb-muted)">{{ $submission->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="text-muted-fb" style="font-size:12px">{{ $submission->ip_address ?? '—' }}</td>
                            @foreach($form->fields as $field)
                                @php
                                    $value = $submission->data[$field->name] ?? null;
                                    $display = is_array($value) ? implode(', ', array_map('strval', array_filter((array)$value, 'is_scalar'))) : (string)($value ?? '');
                                @endphp
                                <td>
                                    <span class="data-cell" title="{{ htmlspecialchars($display) }}">
                                        @if(str_starts_with($display, 'form-builder/') || str_starts_with($display, 'public/'))
                                            <a href="{{ asset('storage/' . ltrim($display, 'public/')) }}" target="_blank" style="color:var(--fb-accent)">
                                                <i class="bi bi-paperclip"></i> View File
                                            </a>
                                        @elseif($display === '')
                                            <span class="text-muted-fb">—</span>
                                        @else
                                            {{ Str::limit($display, 40) }}
                                        @endif
                                    </span>
                                </td>
                            @endforeach
                            <td>
                                <form action="{{ route('form-builder.admin.submission.destroy', $submission->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this submission?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-fb-danger" style="padding:4px 10px;font-size:12px">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
