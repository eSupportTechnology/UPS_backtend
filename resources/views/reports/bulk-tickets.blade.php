<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Tickets Export Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #2196F3;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }
        .section-title {
            background-color: #2196F3;
            color: white;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-item {
            padding: 10px;
            background-color: #f5f5f5;
            border-left: 4px solid #2196F3;
        }
        .summary-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #2196F3;
        }
        .breakdown {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .breakdown-section {
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .breakdown-section h3 {
            margin: 0 0 10px 0;
            font-size: 11px;
            color: #2196F3;
        }
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 10px;
        }
        .breakdown-item:last-child {
            border-bottom: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2196F3;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge, .priority-badge, .type-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }
        .type-personal { background-color: #e8d4f8; color: #6b21a8; }
        .type-company { background-color: #dbeafe; color: #1e40af; }

        .status-open { background-color: #fef3c7; color: #92400e; }
        .status-assigned { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #fed7aa; color: #9a3412; }
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
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Ticket Export Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<div class="section-title">Summary Statistics</div>
<div class="summary">
    <div class="summary-item">
        <div class="label">Total Tickets</div>
        <div class="value">{{ $total_tickets }}</div>
    </div>
    <div class="summary-item">
        <div class="label">Personal Customers</div>
        <div class="value">{{ $personal_count }}</div>
    </div>
    <div class="summary-item">
        <div class="label">Company Customers</div>
        <div class="value">{{ $company_count }}</div>
    </div>
</div>

<div class="breakdown">
    <div class="breakdown-section">
        <h3>Status Breakdown</h3>
        @foreach($status_breakdown as $status => $count)
            <div class="breakdown-item">
                <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                <span style="font-weight: bold;">{{ $count }}</span>
            </div>
        @endforeach
    </div>
    <div class="breakdown-section">
        <h3>Priority Breakdown</h3>
        @foreach($priority_breakdown as $priority => $count)
            <div class="breakdown-item">
                <span>{{ ucfirst($priority) }}</span>
                <span style="font-weight: bold;">{{ $count }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="section-title">Detailed Ticket List</div>
<table>
    <thead>
    <tr>
        <th style="width: 10%;">Ticket ID</th>
        <th style="width: 15%;">Title</th>
        <th style="width: 10%;">Customer</th>
        <th style="width: 8%;">Type</th>
        <th style="width: 9%;">Status</th>
        <th style="width: 8%;">Priority</th>
        <th style="width: 12%;">Location</th>
        <th style="width: 12%;">Created Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ substr($ticket->id, 0, 8) }}...</td>
            <td>{{ substr($ticket->title ?? 'N/A', 0, 30) }}</td>
            <td>{{ substr($ticket->customer_name ?? 'N/A', 0, 20) }}</td>
            <td>
                <span class="type-badge type-{{ $ticket->customer_type ?? 'personal' }}">
                    {{ ucfirst($ticket->customer_type ?? 'Personal') }}
                </span>
            </td>
            <td>
                <span class="status-badge status-{{ $ticket->status ?? 'open' }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'Open')) }}
                </span>
            </td>
            <td>
                <span class="priority-badge priority-{{ $ticket->priority ?? 'medium' }}">
                    {{ ucfirst($ticket->priority ?? 'Medium') }}
                </span>
            </td>
            <td>
                @if($ticket->customer_type === 'company')
                    {{ $ticket->branch_name ?? 'N/A' }}
                @else
                    {{ substr($ticket->address ?? 'N/A', 0, 25) }}
                @endif
            </td>
            <td>{{ $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>This is a computer-generated report. No signature required.</p>
    <p>&copy; {{ date('Y') }} Ticket Management System. All rights reserved.</p>
</div>
</body>
</html>
