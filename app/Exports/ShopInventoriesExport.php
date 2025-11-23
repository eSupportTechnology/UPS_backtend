<?php

namespace App\Exports;

use App\Models\ShopInventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ShopInventoriesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = ShopInventory::query()
            ->select([
                'id',
                'product_name',
                'brand',
                'model',
                'serial_number',
                'category',
                'description',
                'quantity',
                'unit_price',
                'purchase_date',
                'warranty',
                'created_at',
            ]);

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Brand',
            'Model',
            'Serial Number',
            'Category',
            'Description',
            'Quantity',
            'Unit Price',
            'Total Value',
            'Purchase Date',
            'Warranty',
            'Status',
            'Created At',
        ];
    }

    public function map($inventory): array
    {
        $totalValue = $inventory->quantity * $inventory->unit_price;

        $status = 'In Stock';
        if ($inventory->quantity <= 0) {
            $status = 'Out of Stock';
        } elseif ($inventory->quantity <= 10) {
            $status = 'Low Stock';
        }

        return [
            substr($inventory->id, 0, 8) . '...',
            $inventory->product_name ?? 'N/A',
            $inventory->brand ?? 'N/A',
            $inventory->model ?? 'N/A',
            $inventory->serial_number ?? 'N/A',
            $inventory->category ?? 'N/A',
            $inventory->description ?? 'N/A',
            $inventory->quantity,
            number_format($inventory->unit_price, 2),
            number_format($totalValue, 2),
            $inventory->purchase_date ? date('Y-m-d', strtotime($inventory->purchase_date)) : 'N/A',
            $inventory->warranty ?? 'N/A',
            $status,
            $inventory->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 35,
            'H' => 12,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 20,
            'M' => 15,
            'N' => 20,
        ];
    }

    public function title(): string
    {
        return 'Shop Inventories';
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

        if (!empty($this->filters['brand'])) {
            $query->where('brand', $this->filters['brand']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('purchase_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('purchase_date', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['low_stock']) && $this->filters['low_stock']) {
            $threshold = $this->filters['stock_threshold'] ?? 10;
            $query->where('quantity', '<=', $threshold);
        }
    }
}
