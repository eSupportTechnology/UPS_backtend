<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AMC Contracts Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .summary-item strong {
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
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>AMC Contracts Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<div class="summary">
    <div class="summary-item">
        <strong>Total Contracts:</strong> {{ $contracts->count() }}
    </div>
    <div class="summary-item">
        <strong>Active:</strong> {{ $active_count }}
    </div>
    <div class="summary-item">
        <strong>Inactive:</strong> {{ $inactive_count }}
    </div>
    <div class="summary-item">
        <strong>Total Amount:</strong> ${{ number_format($total_amount, 2) }}
    </div>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 8%;">Contract ID</th>
        <th style="width: 12%;">Branch</th>
        <th style="width: 15%;">Customer</th>
        <th style="width: 12%;">Type</th>
        <th style="width: 10%;">Purchase Date</th>
        <th style="width: 10%;">Warranty End</th>
        <th style="width: 10%;" class="amount">Amount</th>
        <th style="width: 8%;">Maintenance</th>
        <th style="width: 8%;">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($contracts as $contract)
        <tr>
            <td>{{ substr($contract->id, 0, 8) }}...</td>
            <td>{{ $contract->branch->branch_name ?? 'N/A' }}</td>
            <td>{{ $contract->customer->name ?? 'N/A' }}</td>
            <td>{{ $contract->contract_type }}</td>
            <td>{{ $contract->purchase_date ? date('Y-m-d', strtotime($contract->purchase_date)) : 'N/A' }}</td>
            <td>{{ $contract->warranty_end_date ? date('Y-m-d', strtotime($contract->warranty_end_date)) : 'N/A' }}</td>
            <td class="amount">${{ number_format($contract->contract_amount, 2) }}</td>
            <td style="text-align: center;">{{ $contract->maintenances->count() }}</td>
            <td>
                    <span class="{{ $contract->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $contract->is_active ? 'Active' : 'Inactive' }}
                    </span>
            </td>
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
