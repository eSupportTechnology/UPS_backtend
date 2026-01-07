<?php

namespace App\Exports;

use App\Models\JobPlannedMaterial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class MaterialsUsageExport implements
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
        $query = JobPlannedMaterial::with(['ticket', 'inventory']);

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Material ID',
            'Job Number',
            'Job Title',
            'Customer Name',
            'Product Name',
            'Brand',
            'Category',
            'Quantity Used',
            'Job Status',
            'Date Added',
            'Job Created',
            'Job Completed',
        ];
    }

    public function map($material): array
    {
        $ticket = $material->ticket;

        return [
            substr($material->id, 0, 8) . '...',
            $ticket->job_number ?? 'N/A',
            $ticket->title ?? 'N/A',
            $ticket->customer_name ?? 'N/A',
            $material->product_name ?? 'N/A',
            $material->brand ?? 'N/A',
            $material->category ?? 'N/A',
            $material->quantity,
            $this->formatStatus($ticket->status ?? ''),
            $material->created_at ? $material->created_at->format('Y-m-d H:i') : 'N/A',
            $ticket->created_at ? $ticket->created_at->format('Y-m-d') : 'N/A',
            $ticket->completed_at ? date('Y-m-d', strtotime($ticket->completed_at)) : '-',
        ];
    }

    private function formatStatus(string $status): string
    {
        $statusMap = [
            'pending_inspection' => 'Pending Inspection',
            'inspected' => 'Inspected',
            'quoted' => 'Quoted',
            'approved_for_repair' => 'Approved',
            'in_repair' => 'In Repair',
            'completed' => 'Completed',
            'quote_rejected' => 'Rejected',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Material ID
            'B' => 15,  // Job Number
            'C' => 25,  // Job Title
            'D' => 20,  // Customer Name
            'E' => 25,  // Product Name
            'F' => 15,  // Brand
            'G' => 15,  // Category
            'H' => 12,  // Quantity
            'I' => 15,  // Job Status
            'J' => 18,  // Date Added
            'K' => 15,  // Job Created
            'L' => 15,  // Job Completed
        ];
    }

    public function title(): string
    {
        return 'Materials Usage';
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhereHas('ticket', function($tq) use ($search) {
                        $tq->where('job_number', 'LIKE', "%{$search}%")
                            ->orWhere('customer_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

        if (!empty($this->filters['brand'])) {
            $query->where('brand', $this->filters['brand']);
        }

        if (!empty($this->filters['job_id'])) {
            $query->where('ticket_id', $this->filters['job_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->whereHas('ticket', function($q) {
                if (is_array($this->filters['status'])) {
                    $q->whereIn('status', $this->filters['status']);
                } else {
                    $statuses = explode(',', $this->filters['status']);
                    $q->whereIn('status', $statuses);
                }
            });
        }

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['today']) && $this->filters['today'] === 'true') {
            $query->whereDate('created_at', now()->toDateString());
        }
    }
}
