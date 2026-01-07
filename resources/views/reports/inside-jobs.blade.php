<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inside Jobs Report</title>
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
            background-color: #2563EB;
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
            width: 100%;
        }
        .summary-grid table {
            width: 100%;
            border: none;
        }
        .summary-grid td {
            width: 12.5%;
            text-align: center;
            padding: 8px;
            background-color: white;
            border: 1px solid #eee;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2563EB;
        }
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.main-table th {
            background-color: #2563EB;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
        }
        table.main-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        table.main-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge, .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending_inspection { background-color: #dbeafe; color: #1e40af; }
        .status-inspected { background-color: #fef3c7; color: #92400e; }
        .status-quoted { background-color: #e9d5ff; color: #7c3aed; }
        .status-approved_for_repair { background-color: #d1fae5; color: #065f46; }
        .status-in_repair { background-color: #fed7aa; color: #9a3412; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-quote_rejected { background-color: #fee2e2; color: #991b1b; }

        .priority-low { background-color: #dbeafe; color: #1e40af; }
        .priority-medium { background-color: #fef3c7; color: #92400e; }
        .priority-high { background-color: #fed7aa; color: #9a3412; }
        .priority-urgent { background-color: #fee2e2; color: #991b1b; }

        .filters-applied {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            font-size: 9px;
        }
        .filters-applied strong {
            color: #92400e;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Inside Jobs Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

@if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['priority']) || !empty($filters['from_date']) || !empty($filters['to_date']) || !empty($filters['today']))
<div class="filters-applied">
    <strong>Filters Applied:</strong>
    @if(!empty($filters['search'])) Search: "{{ $filters['search'] }}" | @endif
    @if(!empty($filters['status'])) Status: {{ is_array($filters['status']) ? implode(', ', $filters['status']) : $filters['status'] }} | @endif
    @if(!empty($filters['priority'])) Priority: {{ $filters['priority'] }} | @endif
    @if(!empty($filters['from_date'])) From: {{ $filters['from_date'] }} | @endif
    @if(!empty($filters['to_date'])) To: {{ $filters['to_date'] }} | @endif
    @if(!empty($filters['today'])) Today Only @endif
</div>
@endif

<div class="summary">
    <div class="summary-grid">
        <table>
            <tr>
                <td>
                    <div class="summary-item">
                        <div class="label">Total Jobs</div>
                        <div class="value">{{ $total_jobs }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Pending Inspection</div>
                        <div class="value" style="color: #3b82f6;">{{ $pending_inspection_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Inspected</div>
                        <div class="value" style="color: #f59e0b;">{{ $inspected_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Quoted</div>
                        <div class="value" style="color: #8b5cf6;">{{ $quoted_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Approved</div>
                        <div class="value" style="color: #10b981;">{{ $approved_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">In Repair</div>
                        <div class="value" style="color: #f97316;">{{ $in_repair_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Completed</div>
                        <div class="value" style="color: #059669;">{{ $completed_count }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Rejected</div>
                        <div class="value" style="color: #dc2626;">{{ $rejected_count }}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="summary-item">
                        <div class="label">Total Quote Value</div>
                        <div class="value" style="color: #059669;">Rs. {{ number_format($total_quote_value, 2) }}</div>
                    </div>
                </td>
                <td colspan="2">
                    <div class="summary-item">
                        <div class="label">High Priority</div>
                        <div class="value" style="color: #f97316;">{{ $high_priority_count }}</div>
                    </div>
                </td>
                <td colspan="2">
                    <div class="summary-item">
                        <div class="label">Urgent</div>
                        <div class="value" style="color: #dc2626;">{{ $urgent_priority_count }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<table class="main-table">
    <thead>
    <tr>
        <th style="width: 8%;">Job #</th>
        <th style="width: 12%;">Customer</th>
        <th style="width: 10%;">UPS Details</th>
        <th style="width: 10%;">Status</th>
        <th style="width: 7%;">Priority</th>
        <th style="width: 10%;">Technician</th>
        <th style="width: 9%;">Quote</th>
        <th style="width: 7%;">Materials</th>
        <th style="width: 9%;">Created</th>
        <th style="width: 9%;">Completed</th>
        <th style="width: 9%;">Duration</th>
    </tr>
    </thead>
    <tbody>
    @foreach($jobs as $job)
        @php
            $duration = '-';
            if ($job->completed_at) {
                $start = \Carbon\Carbon::parse($job->created_at);
                $end = \Carbon\Carbon::parse($job->completed_at);
                $days = $start->diffInDays($end);
                $hours = $start->diffInHours($end) % 24;
                $duration = $days > 0 ? $days . 'd ' . $hours . 'h' : $hours . 'h';
            }
            $materialsCount = $job->plannedMaterials ? $job->plannedMaterials->count() : 0;
        @endphp
        <tr>
            <td>{{ $job->job_number ?? 'N/A' }}</td>
            <td>
                {{ $job->customer_name ?? ($job->customer->name ?? 'N/A') }}<br>
                <span style="color: #666; font-size: 7px;">{{ $job->customer_phone ?? ($job->customer->phone ?? '') }}</span>
            </td>
            <td>
                {{ $job->ups_brand ?? '' }} {{ $job->ups_model ?? '' }}<br>
                <span style="color: #666; font-size: 7px;">{{ $job->ups_serial_number ?? '' }}</span>
            </td>
            <td>
                <span class="status-badge status-{{ $job->status }}">
                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                </span>
            </td>
            <td>
                <span class="priority-badge priority-{{ $job->priority ?? 'medium' }}">
                    {{ ucfirst($job->priority ?? 'Medium') }}
                </span>
            </td>
            <td>{{ $job->assignedTechnician->name ?? 'Not Assigned' }}</td>
            <td style="text-align: right;">
                @if($job->quote_total)
                    Rs. {{ number_format($job->quote_total, 2) }}
                @else
                    -
                @endif
            </td>
            <td style="text-align: center;">{{ $materialsCount }}</td>
            <td>{{ $job->created_at ? $job->created_at->format('Y-m-d') : 'N/A' }}</td>
            <td>{{ $job->completed_at ? date('Y-m-d', strtotime($job->completed_at)) : '-' }}</td>
            <td style="text-align: center;">{{ $duration }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>This is a computer-generated report. No signature required.</p>
    <p>&copy; {{ date('Y') }} UPS Management System. All rights reserved.</p>
</div>
</body>
</html>
