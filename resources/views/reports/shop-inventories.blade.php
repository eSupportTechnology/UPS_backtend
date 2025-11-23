<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shop Inventories Report</title>
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
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
        }
        .summary-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 18px;
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
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        .status-low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
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
    <h1>Shop Inventories Report</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<div class="summary">
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Items</div>
            <div class="value">{{ $total_items }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Quantity</div>
            <div class="value">{{ $total_quantity }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Value</div>
            <div class="value">Rs {{ number_format($total_value, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Low Stock Items</div>
            <div class="value" style="color: #f59e0b;">{{ $low_stock_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Out of Stock</div>
            <div class="value" style="color: #ef4444;">{{ $out_of_stock_count }}</div>
        </div>
        <div class="summary-item">
            <div class="label">In Stock Items</div>
            <div class="value" style="color: #10b981;">{{ $total_items - $out_of_stock_count }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 15%;">Product Name</th>
        <th style="width: 10%;">Brand</th>
        <th style="width: 10%;">Model</th>
        <th style="width: 12%;">Serial Number</th>
        <th style="width: 10%;">Category</th>
        <th style="width: 7%;">Qty</th>
        <th style="width: 10%;" class="amount">Unit Price</th>
        <th style="width: 10%;" class="amount">Total Value</th>
        <th style="width: 10%;">Purchase Date</th>
        <th style="width: 8%;">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($inventories as $inventory)
        @php
            $totalValue = $inventory->quantity * $inventory->unit_price;
            $status = 'In Stock';
            $statusClass = 'status-in-stock';

            if ($inventory->quantity <= 0) {
                $status = 'Out of Stock';
                $statusClass = 'status-out-of-stock';
            } elseif ($inventory->quantity <= 10) {
                $status = 'Low Stock';
                $statusClass = 'status-low-stock';
            }
        @endphp
        <tr>
            <td>{{ $inventory->product_name }}</td>
            <td>{{ $inventory->brand ?? 'N/A' }}</td>
            <td>{{ $inventory->model ?? 'N/A' }}</td>
            <td>{{ $inventory->serial_number ?? 'N/A' }}</td>
            <td>{{ $inventory->category ?? 'N/A' }}</td>
            <td style="text-align: center;">{{ $inventory->quantity }}</td>
            <td class="amount">Rs {{ number_format($inventory->unit_price, 2) }}</td>
            <td class="amount">Rs {{ number_format($totalValue, 2) }}</td>
            <td>{{ $inventory->purchase_date ? date('Y-m-d', strtotime($inventory->purchase_date)) : 'N/A' }}</td>
            <td>
                    <span class="status-badge {{ $statusClass }}">
                        {{ $status }}
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
