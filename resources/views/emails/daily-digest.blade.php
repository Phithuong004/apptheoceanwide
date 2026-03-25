<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg,#4f46e5,#7c3aed); padding: 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .task-item { padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 8px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .urgent { background: #fef3c7; color: #92400e; }
        .overdue { background: #fee2e2; color: #991b1b; }
        .footer { background: #f3f4f6; padding: 16px 32px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Daily Digest</h1>
        <p style="color:#c4b5fd;margin:4px 0 0">{{ now()->format('l, d/m/Y') }}</p>
    </div>
    <div class="body">
        <p>Chào <strong>{{ $user->name }}</strong>,</p>

        @if($dueToday->count() > 0)
        <h3 style="color:#1f2937">📅 Due hôm nay ({{ $dueToday->count() }})</h3>
        @foreach($dueToday as $task)
        <div class="task-item">
            <strong>{{ $task->title }}</strong>
            <br><small style="color:#6b7280">{{ $task->project->name }}</small>
            <span class="badge urgent">Due today</span>
        </div>
        @endforeach
        @endif

        @if($overdue->count() > 0)
        <h3 style="color:#dc2626">⚠️ Quá hạn ({{ $overdue->count() }})</h3>
        @foreach($overdue as $task)
        <div class="task-item" style="border-color:#fca5a5">
            <strong>{{ $task->title }}</strong>
            <span class="badge overdue">Overdue {{ $task->due_date->diffForHumans() }}</span>
        </div>
        @endforeach
        @endif

        <p style="margin-top:24px">
            <a href="{{ url('/') }}" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block">
                Xem tất cả task →
            </a>
        </p>
    </div>
    <div class="footer">
        {{ config('app.name') }} · <a href="#" style="color:#6b7280">Bỏ đăng ký</a>
    </div>
</div>
</body>
</html>
