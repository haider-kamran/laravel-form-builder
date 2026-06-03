@extends('form-builder::admin.layout')
@section('page-title', 'All Forms')

@section('topbar-actions')
    <a href="{{ route('form-builder.admin.create') }}" class="btn-fb-primary">
        <i class="bi bi-plus-lg"></i> New Form
    </a>
@endsection

@section('content')

{{-- STATS ROW --}}
<div class="row g-3 mb-4">
    @php
        $total    = $forms->count();
        $active   = $forms->where('is_active', true)->count();
        $inactive = $total - $active;
        $submissions = \Hyderkamran\FormBuilder\Models\FormSubmission::count();
    @endphp
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(108,99,255,.15);color:#6c63ff"><i class="bi bi-ui-checks-grid"></i></div>
            <div><div class="fb-stat-label">Total Forms</div><div class="fb-stat-value">{{ $total }}</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(34,197,94,.15);color:#22c55e"><i class="bi bi-check-circle"></i></div>
            <div><div class="fb-stat-label">Active</div><div class="fb-stat-value">{{ $active }}</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(100,116,139,.12);color:#64748b"><i class="bi bi-slash-circle"></i></div>
            <div><div class="fb-stat-label">Inactive</div><div class="fb-stat-value">{{ $inactive }}</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="fb-stat">
            <div class="fb-stat-icon" style="background:rgba(245,158,11,.15);color:#f59e0b"><i class="bi bi-send"></i></div>
            <div><div class="fb-stat-label">Submissions</div><div class="fb-stat-value">{{ $submissions }}</div></div>
        </div>
    </div>
</div>

{{-- FORMS TABLE --}}
<div class="fb-card">
    <div class="fb-card-header">
        <span><i class="bi bi-layout-text-window-reverse me-2"></i>Forms</span>
    </div>
    <div class="fb-card-body" style="padding:0">
        @if($forms->isEmpty())
            <div class="text-center py-5 text-muted-fb">
                <i class="bi bi-inbox" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.3"></i>
                No forms yet. <a href="{{ route('form-builder.admin.create') }}" style="color:var(--fb-accent)">Create your first form →</a>
            </div>
        @else
            <table class="fb-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Fields</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forms as $form)
                        <tr>
                            <td class="text-muted-fb">{{ $form->id }}</td>
                            <td style="font-weight:600">{{ $form->title }}</td>
                            <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px;font-size:12px">{{ $form->slug }}</code></td>
                            <td>{{ $form->fields_count ?? $form->fields()->count() }}</td>
                            <td>{{ $form->submissions_count ?? $form->submissions()->count() }}</td>
                            <td>
                                @if($form->is_active)
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td class="text-muted-fb">{{ $form->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('form-builder.admin.edit', $form->id) }}" class="btn-fb-ghost" style="padding:5px 10px;font-size:12px">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="{{ route('form-builder.admin.submissions', $form->id) }}" class="btn-fb-ghost" style="padding:5px 10px;font-size:12px">
                                        <i class="bi bi-table"></i> Submissions
                                    </a>
                                    <form action="{{ route('form-builder.admin.destroy', $form->id) }}" method="POST" onsubmit="return confirm('Delete \'{{ $form->title }}\'? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-fb-danger" style="padding:5px 10px;font-size:12px">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
