<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Materials Usage Report</title>
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
            background-color: #059669;
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
        .summary-grid table {
            width: 100%;
            border: none;
        }
        .summary-grid td {
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
            color: #059669;
        }
        .filters-applied {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #ecfdf5;
            border: 1px solid #6ee7b7;
            font-size: 9px;
        }
        .filters-applied strong {
            color: #065f46;
        }
        .section-title {
            background-color: #059669;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.main-table th {
            background-color: #059669;
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
        .category-summary {
            margin-bottom: 15px;
        }
        .category-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .category-summary th {
            background-color: #10b981;
            color: white;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
        }
        .category-summary td {
            padding: 5px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        .status-badge {
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
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .two-column {
            width: 100%;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Materials Usage Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

@if(!empty($filters['search']) || !empty($filters['category']) || !empty($filters['brand']) || !empty($filters['from_date']) || !empty($filters['to_date']) || !empty($filters['today']))
<div class="filters-applied">
    <strong>Filters Applied:</strong>
    @if(!empty($filters['search'])) Search: "{{ $filters['search'] }}" | @endif
    @if(!empty($filters['category'])) Category: {{ $filters['category'] }} | @endif
    @if(!empty($filters['brand'])) Brand: {{ $filters['brand'] }} | @endif
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
                        <div class="label">Total Material Entries</div>
                        <div class="value">{{ $total_materials }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Total Quantity Used</div>
                        <div class="value">{{ number_format($total_quantity) }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Unique Products</div>
                        <div class="value">{{ $unique_products }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-item">
                        <div class="label">Jobs Using Materials</div>
                        <div class="value">{{ $jobs_count }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- Category and Brand Summary Side by Side -->
<table class="two-column">
    <tr>
        <td>
            @if($category_totals->count() > 0)
            <div class="section-title">Usage by Category</div>
            <div class="category-summary">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th style="text-align: center;">Items</th>
                            <th style="text-align: center;">Total Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category_totals as $category => $data)
                        <tr>
                            <td>{{ $category ?: 'Uncategorized' }}</td>
                            <td style="text-align: center;">{{ $data['count'] }}</td>
                            <td style="text-align: center;">{{ $data['total_quantity'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </td>
        <td>
            @if($brand_totals->count() > 0)
            <div class="section-title">Usage by Brand</div>
            <div class="category-summary">
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th style="text-align: center;">Items</th>
                            <th style="text-align: center;">Total Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brand_totals as $brand => $data)
                        <tr>
                            <td>{{ $brand ?: 'Unknown' }}</td>
                            <td style="text-align: center;">{{ $data['count'] }}</td>
                            <td style="text-align: center;">{{ $data['total_quantity'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </td>
    </tr>
</table>

<div class="section-title">Detailed Materials List</div>

<table class="main-table">
    <thead>
    <tr>
        <th style="width: 10%;">Job #</th>
        <th style="width: 15%;">Customer</th>
        <th style="width: 20%;">Product Name</th>
        <th style="width: 12%;">Brand</th>
        <th style="width: 12%;">Category</th>
        <th style="width: 8%;">Qty</th>
        <th style="width: 10%;">Job Status</th>
        <th style="width: 13%;">Date Added</th>
    </tr>
    </thead>
    <tbody>
    @foreach($materials as $material)
        <tr>
            <td>{{ $material->ticket->job_number ?? 'N/A' }}</td>
            <td>{{ $material->ticket->customer_name ?? 'N/A' }}</td>
            <td>{{ $material->product_name }}</td>
            <td>{{ $material->brand ?? '-' }}</td>
            <td>{{ $material->category ?? '-' }}</td>
            <td style="text-align: center; font-weight: bold;">{{ $material->quantity }}</td>
            <td>
                <span class="status-badge status-{{ $material->ticket->status ?? '' }}">
                    {{ ucfirst(str_replace('_', ' ', $material->ticket->status ?? '')) }}
                </span>
            </td>
            <td>{{ $material->created_at ? $material->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
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
