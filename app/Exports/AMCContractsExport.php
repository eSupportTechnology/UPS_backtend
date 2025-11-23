<?php

namespace App\Exports;

use App\Models\AMCContract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class AMCContractsExport implements
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
        $query = AMCContract::with(['branch', 'customer', 'maintenances'])
            ->select([
                'id',
                'branch_id',
                'customer_id',
                'contract_type',
                'purchase_date',
                'warranty_end_date',
                'contract_amount',
                'notes',
                'is_active',
                'created_at',
            ]);

        $this->applyFilters($query);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Contract ID',
            'Branch',
            'Customer Name',
            'Customer Email',
            'Contract Type',
            'Purchase Date',
            'Warranty End Date',
            'Contract Amount',
            'Maintenance Count',
            'Status',
            'Notes',
            'Created At',
        ];
    }

    public function map($contract): array
    {
        return [
            $contract->id,
            $contract->branch->branch_name ?? 'N/A',
            $contract->customer->name ?? 'N/A',
            $contract->customer->email ?? 'N/A',
            $contract->contract_type,
            $contract->purchase_date ? date('Y-m-d', strtotime($contract->purchase_date)) : 'N/A',
            $contract->warranty_end_date ? date('Y-m-d', strtotime($contract->warranty_end_date)) : 'N/A',
            number_format($contract->contract_amount, 2),
            $contract->maintenances->count(),
            $contract->is_active ? 'Active' : 'Inactive',
            $contract->notes ?? 'N/A',
            $contract->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 38,
            'B' => 20,
            'C' => 25,
            'D' => 30,
            'E' => 20,
            'F' => 15,
            'G' => 18,
            'H' => 18,
            'I' => 18,
            'J' => 12,
            'K' => 30,
            'L' => 20,
        ];
    }

    public function title(): string
    {
        return 'AMC Contracts';
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('contract_type', 'LIKE', "%{$search}%")
                    ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($this->filters['branch_id'])) {
            $query->where('branch_id', $this->filters['branch_id']);
        }

        if (!empty($this->filters['customer_id'])) {
            $query->where('customer_id', $this->filters['customer_id']);
        }

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }
    }
}
