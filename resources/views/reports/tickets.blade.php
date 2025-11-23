<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tickets Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #4472C4;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #4472C4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge, .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-assigned { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-in_progress { background-color: #e0e7ff; color: #3730a3; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }

        .priority-low { background-color: #dbeafe; color: #1e40af; }
        .priority-medium { background-color: #fef3c7; color: #92400e; }
        .priority-high { background-color: #fed7aa; color: #9a3412; }
        .priority-urgent { background-color: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Tickets Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<div class="summary">
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Tickets</div>
            <div class="value">{{ $total_tickets }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Pending</div>
            <div class="value" style="color: #f59e0b;">{{ $pending_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Assigned</div>
            <div class="value" style="color: #3b82f6;">{{ $assigned_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">In Progress</div>
            <div class="value" style="color: #8b5cf6;">{{ $in_progress_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Completed</div>
            <div class="value" style="color: #10b981;">{{ $completed_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Cancelled</div>
            <div class="value" style="color: #ef4444;">{{ $cancelled_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">High Priority</div>
            <div class="value" style="color: #f97316;">{{ $high_priority_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Urgent</div>
            <div class="value" style="color: #dc2626;">{{ $urgent_priority_count }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 8%;">Ticket ID</th>
        <th style="width: 15%;">Title</th>
        <th style="width: 8%;">Status</th>
        <th style="width: 8%;">Priority</th>
        <th style="width: 12%;">Customer</th>
        <th style="width: 12%;">Technician</th>
        <th style="width: 10%;">District</th>
        <th style="width: 10%;">City</th>
        <th style="width: 10%;">Created</th>
        <th style="width: 7%;">Duration</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tickets as $ticket)
        @php
            $duration = '-';
            if ($ticket->completed_at) {
                $start = \Carbon\Carbon::parse($ticket->created_at);
                $end = \Carbon\Carbon::parse($ticket->completed_at);
                $days = $start->diffInDays($end);
                $duration = $days . 'd';
            }
        @endphp
        <tr>
            <td>{{ substr($ticket->id, 0, 8) }}...</td>
            <td>{{ $ticket->title }}</td>
            <td>
                    <span class="status-badge status-{{ $ticket->status }}">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
            </td>
            <td>
                    <span class="priority-badge priority-{{ $ticket->priority }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
            </td>
            <td>{{ $ticket->customer_name ?? 'N/A' }}</td>
            <td>{{ $ticket->technician_name ?? 'Not Assigned' }}</td>
            <td>{{ $ticket->district ?? 'N/A' }}</td>
            <td>{{ $ticket->city ?? 'N/A' }}</td>
            <td>{{ $ticket->created_at ? $ticket->created_at->format('Y-m-d') : 'N/A' }}</td>
            <td style="text-align: center;">{{ $duration }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>This is a computer-generated report. No signature required.</p>
    <p>&copy; {{ date('Y') }} Maintenance System. All rights reserved.</p>
</div>
</body>
</html>
