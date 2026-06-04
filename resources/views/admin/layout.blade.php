<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Forms') — Form Builder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --fb-bg: #0f1117;
            --fb-surface: #1a1d27;
            --fb-surface-2: #22253a;
            --fb-border: rgba(255,255,255,.07);
            --fb-accent: #6c63ff;
            --fb-accent-soft: rgba(108,99,255,.15);
            --fb-success: #22c55e;
            --fb-danger: #ef4444;
            --fb-warning: #f59e0b;
            --fb-text: #e2e8f0;
            --fb-muted: #64748b;
            --fb-radius: 12px;
            --fb-topbar-bg: rgba(15,17,23,.85);
            --fb-table-hover: rgba(255,255,255,.02);
        }

        @media (prefers-color-scheme: light) {
            :root {
                --fb-bg: #f8fafc;
                --fb-surface: #ffffff;
                --fb-surface-2: #f1f5f9;
                --fb-border: rgba(0,0,0,.08);
                --fb-accent-soft: rgba(108,99,255,.12);
                --fb-text: #1e293b;
                --fb-muted: #64748b;
                --fb-topbar-bg: rgba(255,255,255,.85);
                --fb-table-hover: rgba(0,0,0,.02);
            }
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--fb-bg);
            color: var(--fb-text);
            font-size: 14px;
        }

        /* ── SIDEBAR ──────────────────────────────────────────────── */
        .fb-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: 240px;
            background: var(--fb-surface);
            border-right: 1px solid var(--fb-border);
            display: flex; flex-direction: column;
            z-index: 100;
            overflow-y: auto;
        }
        .fb-sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--fb-border);
            display: flex; align-items: center; gap: 10px;
        }
        .fb-sidebar-brand .brand-icon {
            width: 34px; height: 34px;
            background: var(--fb-accent);
            border-radius: 8px;
            display: grid; place-items: center;
            font-size: 18px; color: #fff;
        }
        .fb-sidebar-brand .brand-name { font-size: 15px; font-weight: 700; letter-spacing: -.3px; }
        .fb-sidebar-brand .brand-version { font-size: 10px; color: var(--fb-muted); }
        .fb-nav { padding: 14px 12px; flex: 1; }
        .fb-nav-label { font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--fb-muted); padding: 6px 8px 4px; }
        .fb-nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: var(--fb-muted); text-decoration: none;
            font-weight: 500; transition: all .15s;
            margin-bottom: 2px; cursor: pointer;
        }
        .fb-nav-item:hover, .fb-nav-item.active {
            background: var(--fb-accent-soft);
            color: var(--fb-accent);
        }
        .fb-nav-item i { font-size: 16px; }

        /* ── MAIN CONTENT ─────────────────────────────────────────── */
        .fb-main {
            margin-left: 240px;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .fb-topbar {
            position: sticky; top: 0; z-index: 50;
            background: var(--fb-topbar-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--fb-border);
            padding: 14px 28px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .fb-topbar .page-title { font-size: 18px; font-weight: 700; letter-spacing: -.4px; }
        .fb-content { padding: 28px; flex: 1; }

        /* ── CARDS ────────────────────────────────────────────────── */
        .fb-card {
            background: var(--fb-surface);
            border: 1px solid var(--fb-border);
            border-radius: var(--fb-radius);
            overflow: hidden;
        }
        .fb-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--fb-border);
            display: flex; align-items: center; justify-content: space-between;
            font-weight: 600;
        }
        .fb-card-body { padding: 20px; }

        /* ── BADGES ───────────────────────────────────────────────── */
        .badge-active   { background: rgba(34,197,94,.15);  color: #22c55e; border-radius: 50px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
        .badge-inactive { background: rgba(100,116,139,.15); color: var(--fb-muted); border-radius: 50px; padding: 3px 10px; font-size: 12px; font-weight: 600; }

        /* ── TABLE ────────────────────────────────────────────────── */
        .fb-table { width: 100%; border-collapse: collapse; }
        .fb-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: var(--fb-muted); padding: 10px 16px; border-bottom: 1px solid var(--fb-border); text-align: left; font-weight: 600; }
        .fb-table td { padding: 14px 16px; border-bottom: 1px solid var(--fb-border); vertical-align: middle; }
        .fb-table tr:last-child td { border-bottom: none; }
        .fb-table tr:hover td { background: var(--fb-table-hover); }

        /* ── BUTTONS ──────────────────────────────────────────────── */
        .btn-fb-primary {
            background: var(--fb-accent); color: #fff; border: none;
            border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: opacity .15s; display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
        }
        .btn-fb-primary:hover { opacity: .88; color: #fff; }
        .btn-fb-ghost {
            background: transparent; color: var(--fb-muted); border: 1px solid var(--fb-border);
            border-radius: 8px; padding: 7px 14px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
        }
        .btn-fb-ghost:hover { background: var(--fb-surface-2); color: var(--fb-text); }
        .btn-fb-danger {
            background: rgba(239,68,68,.15); color: var(--fb-danger); border: 1px solid rgba(239,68,68,.25);
            border-radius: 8px; padding: 7px 14px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-fb-danger:hover { background: rgba(239,68,68,.25); }
        .btn-fb-success {
            background: rgba(34,197,94,.15); color: var(--fb-success); border: 1px solid rgba(34,197,94,.25);
            border-radius: 8px; padding: 7px 14px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-fb-success:hover { background: rgba(34,197,94,.25); }

        /* ── FORM CONTROLS ────────────────────────────────────────── */
        .fb-input, .fb-select, .fb-textarea {
            background: var(--fb-surface-2);
            border: 1px solid var(--fb-border);
            color: var(--fb-text);
            border-radius: 8px;
            padding: 9px 14px;
            width: 100%;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            transition: border-color .15s;
            outline: none;
        }
        .fb-input:focus, .fb-select:focus, .fb-textarea:focus {
            border-color: var(--fb-accent);
            box-shadow: 0 0 0 3px var(--fb-accent-soft);
        }
        .fb-input::placeholder { color: var(--fb-muted); }
        .fb-label { display: block; font-size: 12px; font-weight: 600; color: var(--fb-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
        .fb-select option { background: var(--fb-surface-2); }
        .fb-textarea { resize: vertical; min-height: 80px; }

        /* ── ALERTS ───────────────────────────────────────────────── */
        .fb-alert {
            border-radius: 10px; padding: 12px 16px; font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 20px;
        }
        .fb-alert-success { background: rgba(34,197,94,.1); color: var(--fb-success); border: 1px solid rgba(34,197,94,.2); }
        .fb-alert-danger   { background: rgba(239,68,68,.1);  color: var(--fb-danger);  border: 1px solid rgba(239,68,68,.2); }

        /* ── STAT CARDS ───────────────────────────────────────────── */
        .fb-stat {
            background: var(--fb-surface); border: 1px solid var(--fb-border);
            border-radius: var(--fb-radius); padding: 20px 22px;
            display: flex; align-items: center; gap: 16px;
        }
        .fb-stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: grid; place-items: center; font-size: 20px; flex-shrink: 0;
        }
        .fb-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .6px; color: var(--fb-muted); font-weight: 600; }
        .fb-stat-value { font-size: 26px; font-weight: 700; letter-spacing: -1px; margin-top: 2px; }

        /* ── MISC ─────────────────────────────────────────────────── */
        .text-muted-fb  { color: var(--fb-muted); }
        .divider { border: none; border-top: 1px solid var(--fb-border); margin: 0; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--fb-surface); }
        ::-webkit-scrollbar-thumb { background: var(--fb-border); border-radius: 10px; }
        @yield('extra-styles')
    </style>
    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="fb-sidebar">
    <div class="fb-sidebar-brand">
        <div class="brand-icon"><i class="bi bi-ui-checks-grid"></i></div>
        <div>
            <div class="brand-name">FormBuilder</div>
            <div class="brand-version">v1.0.0 — Admin</div>
        </div>
    </div>
    <nav class="fb-nav">
        <div class="fb-nav-label">Forms</div>
        <a href="{{ route('form-builder.admin.index') }}" class="fb-nav-item {{ request()->routeIs('form-builder.admin.index') ? 'active' : '' }}">
            <i class="bi bi-layout-text-window-reverse"></i> All Forms
        </a>
        <a href="{{ route('form-builder.admin.create') }}" class="fb-nav-item {{ request()->routeIs('form-builder.admin.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> New Form
        </a>
        @stack('sidebar-items')
    </nav>
</aside>

{{-- MAIN --}}
<div class="fb-main">
    {{-- TOP BAR --}}
    <header class="fb-topbar">
        <span class="page-title">@yield('page-title', 'Forms')</span>
        <div class="d-flex align-items-center gap-2">
            @yield('topbar-actions')
        </div>
    </header>

    {{-- CONTENT --}}
    <main class="fb-content">
        @if(session('status'))
            <div class="fb-alert fb-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="fb-alert fb-alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ $errors->first() }}
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@stack('scripts')
</body>
</html>
