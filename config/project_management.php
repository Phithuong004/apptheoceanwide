<?php
return [
    'task_statuses' => [
        'backlog'     => ['label' => 'Backlog',      'color' => 'gray'],
        'todo'        => ['label' => 'To Do',         'color' => 'blue'],
        'in_progress' => ['label' => 'In Progress',   'color' => 'yellow'],
        'in_review'   => ['label' => 'In Review',     'color' => 'purple'],
        'done'        => ['label' => 'Done',           'color' => 'green'],
        'blocked'     => ['label' => 'Blocked',        'color' => 'red'],
    ],

    'task_priorities' => [
        'low'      => ['label' => 'Low',      'color' => 'gray'],
        'medium'   => ['label' => 'Medium',   'color' => 'blue'],
        'high'     => ['label' => 'High',     'color' => 'yellow'],
        'urgent'   => ['label' => 'Urgent',   'color' => 'orange'],
        'critical' => ['label' => 'Critical', 'color' => 'red'],
    ],

    'story_points' => [1, 2, 3, 5, 8, 13, 21, 34],

    'leave_types' => [
        'annual'    => 'Nghỉ phép năm',
        'sick'      => 'Nghỉ bệnh',
        'maternity' => 'Nghỉ thai sản',
        'paternity' => 'Nghỉ hậu sản (nam)',
        'unpaid'    => 'Nghỉ không lương',
        'other'     => 'Khác',
    ],

    'invoice_terms' => 'Thanh toán trong vòng 30 ngày kể từ ngày xuất hóa đơn. Quá hạn sẽ chịu lãi suất 2%/tháng.',

    'working_hours_per_day' => 8,
    'working_days_per_month' => 22,
    'annual_leave_days' => 12,

    'webhook_events' => [
        'task.created', 'task.updated', 'task.assigned', 'task.completed',
        'sprint.started', 'sprint.completed',
        'project.created', 'project.completed',
        'invoice.sent', 'invoice.paid',
    ],
];
