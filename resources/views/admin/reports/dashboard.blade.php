<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Report &mdash; {{ $company }}</title>
    @if (app_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . app_setting('favicon')) }}">
    @endif
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #333; margin: 0; background: #f5f5f5; }
        .page { max-width: 800px; margin: 24px auto; background: #fff; padding: 40px; border-radius: 12px; }
        .head { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #263592; padding-bottom: 16px; margin-bottom: 24px; }
        .head h1 { margin: 0; font-size: 20px; color: #263592; }
        .head .meta { text-align: right; font-size: 12px; color: #888; }
        .logo { height: 44px; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .5px; color: #263592; margin: 28px 0 12px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .card { border: 1px solid #eee; border-radius: 10px; padding: 14px; }
        .card .label { font-size: 11px; color: #888; text-transform: uppercase; }
        .card .val { font-size: 24px; font-weight: 800; color: #263592; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #eee; }
        th { background: #f7f8fa; color: #555; font-size: 11px; text-transform: uppercase; }
        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .toolbar { max-width: 800px; margin: 16px auto 0; text-align: right; }
        .btn { display: inline-block; background: #263592; color: #fff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; border: 0; cursor: pointer; }
        .btn.secondary { background: #fff; color: #333; border: 1px solid #ccc; margin-right: 8px; }
        @media print { body { background: #fff; } .toolbar { display: none; } .page { margin: 0; border-radius: 0; max-width: none; } }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="{{ route('admin.dashboard') }}" class="btn secondary">← Back</a>
        <a href="{{ route('admin.dashboard.export') }}" class="btn secondary">Export Excel</a>
        <button class="btn" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="page">
        <div class="head">
            <div style="display:flex;align-items:center;gap:12px;">
                @if (app_setting('logo'))
                    <img src="{{ asset('storage/' . app_setting('logo')) }}" class="logo" alt="logo">
                @endif
                <div>
                    <h1>{{ $company }}</h1>
                    <div style="font-size:12px;color:#888;">Dashboard Report</div>
                </div>
            </div>
            <div class="meta">
                Generated<br><strong>{{ $generated_at }}</strong>
            </div>
        </div>

        <h2>Overview</h2>
        <div class="grid">
            <div class="card"><div class="label">Services</div><div class="val">{{ number_format($stats['services']) }}</div></div>
            <div class="card"><div class="label">Projects</div><div class="val">{{ number_format($stats['projects']) }}</div></div>
            <div class="card"><div class="label">Messages</div><div class="val">{{ number_format($stats['messages']) }}</div></div>
            <div class="card"><div class="label">Users</div><div class="val">{{ number_format($stats['users']) }}</div></div>
        </div>

        <div class="two">
            <div>
                <h2>Messages by Status</h2>
                <table>
                    <tr><th>Status</th><th>Count</th></tr>
                    <tr><td>Unread</td><td>{{ $messages['unread'] }}</td></tr>
                    <tr><td>Read</td><td>{{ $messages['read'] }}</td></tr>
                    <tr><td>Replied</td><td>{{ $messages['replied'] }}</td></tr>
                    <tr><td>Archived</td><td>{{ $messages['archived'] }}</td></tr>
                    <tr><td>Spam</td><td>{{ $messages['spam'] }}</td></tr>
                </table>
            </div>
            <div>
                <h2>Projects by Status</h2>
                <table>
                    <tr><th>Status</th><th>Count</th></tr>
                    <tr><td>Planned</td><td>{{ $projects['planned'] }}</td></tr>
                    <tr><td>Ongoing</td><td>{{ $projects['ongoing'] }}</td></tr>
                    <tr><td>Completed</td><td>{{ $projects['completed'] }}</td></tr>
                </table>
            </div>
        </div>

        <h2>Website Visits — Last 12 Months
            <span style="font-weight:400;text-transform:none;color:#888;font-size:12px;">
                ({{ number_format($chart['total_views']) }} views · {{ number_format($chart['total_visitors']) }} unique visitors)
            </span>
        </h2>
        <table>
            <tr><th>Month</th><th>Page Views</th><th>Unique Visitors</th></tr>
            @foreach ($chart['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($chart['views'][$i] ?? 0) }}</td>
                    <td>{{ number_format($chart['visitors'][$i] ?? 0) }}</td>
                </tr>
            @endforeach
        </table>

    </div>

</body>
</html>
