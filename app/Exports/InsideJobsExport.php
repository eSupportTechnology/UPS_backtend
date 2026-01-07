<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class InsideJobsExport implements
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
        $query = Ticket::insideJobs()
            ->with(['customer', 'assignedTechnician', 'inspector', 'quoter', 'plannedMaterials']);

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Job Number',
            'Title',
            'Status',
            'Priority',
            'Customer Name',
            'Customer Phone',
            'UPS Brand',
            'UPS Model',
            'UPS Serial',
            'Assigned Technician',
            'Inspector',
            'Quote Total',
            'Approval Status',
            'Materials Count',
            'Created Date',
            'Completed Date',
            'Rejection Reason',
            'Description',
        ];
    }

    public function map($job): array
    {
        $materialsCount = $job->plannedMaterials ? $job->plannedMaterials->count() : 0;

        return [
            $job->job_number ?? 'N/A',
            $job->title ?? 'N/A',
            $this->formatStatus($job->status),
            ucfirst($job->priority ?? 'N/A'),
            $job->customer_name ?? ($job->customer->name ?? 'N/A'),
            $job->customer_phone ?? ($job->customer->phone ?? 'N/A'),
            $job->ups_brand ?? 'N/A',
            $job->ups_model ?? 'N/A',
            $job->ups_serial_number ?? 'N/A',
            $job->assignedTechnician->name ?? 'Not Assigned',
            $job->inspector->name ?? 'N/A',
            $job->quote_total ? number_format($job->quote_total, 2) : 'N/A',
            ucfirst($job->approval_status ?? 'N/A'),
            $materialsCount,
            $job->created_at ? $job->created_at->format('Y-m-d H:i') : 'N/A',
            $job->completed_at ? date('Y-m-d H:i', strtotime($job->completed_at)) : 'N/A',
            $job->rejection_reason ?? '',
            $job->description ?? 'N/A',
        ];
    }

    private function formatStatus(string $status): string
    {
        $statusMap = [
            'pending_inspection' => 'Pending Inspection',
            'inspected' => 'Inspected',
            'quoted' => 'Quoted',
            'approved_for_repair' => 'Approved for Repair',
            'in_repair' => 'In Repair',
            'completed' => 'Completed',
            'quote_rejected' => 'Quote Rejected',
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
                    'startColor' => ['rgb' => '2563EB']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Job Number
            'B' => 30,  // Title
            'C' => 18,  // Status
            'D' => 12,  // Priority
            'E' => 25,  // Customer Name
            'F' => 15,  // Customer Phone
            'G' => 15,  // UPS Brand
            'H' => 15,  // UPS Model
            'I' => 20,  // UPS Serial
            'J' => 20,  // Technician
            'K' => 20,  // Inspector
            'L' => 12,  // Quote Total
            'M' => 15,  // Approval Status
            'N' => 12,  // Materials Count
            'O' => 18,  // Created Date
            'P' => 18,  // Completed Date
            'Q' => 30,  // Rejection Reason
            'R' => 40,  // Description
        ];
    }

    public function title(): string
    {
        return 'Inside Jobs';
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('job_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('ups_serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            if (is_array($this->filters['status'])) {
                $query->whereIn('status', $this->filters['status']);
            } else {
                $statuses = explode(',', $this->filters['status']);
                $query->whereIn('status', $statuses);
            }
        }

        if (!empty($this->filters['priority'])) {
            $query->where('priority', $this->filters['priority']);
        }

        if (!empty($this->filters['technician'])) {
            $query->where('assigned_to', $this->filters['technician']);
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
