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

class TicketsExport implements
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
        $query = Ticket::select([
            'tickets.id',
            'tickets.customer_id',
            'tickets.title',
            'tickets.description',
            'tickets.status',
            'tickets.priority',
            'tickets.assigned_to',
            'tickets.district',
            'tickets.city',
            'tickets.gramsewa_division',
            'tickets.accepted_at',
            'tickets.completed_at',
            'tickets.created_at',
        ])
            ->join('users', 'tickets.customer_id', '=', 'users.id')
            ->leftJoin('users as technicians', 'tickets.assigned_to', '=', 'technicians.id')
            ->addSelect([
                'users.name as customer_name',
                'users.email as customer_email',
                'users.phone as customer_phone',
                'technicians.name as technician_name',
                'technicians.email as technician_email',
            ]);

        $this->applyFilters($query);

        return $query->orderBy('tickets.created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Title',
            'Status',
            'Priority',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Assigned Technician',
            'Technician Email',
            'District',
            'City',
            'Gramsewa Division',
            'Created At',
            'Accepted At',
            'Completed At',
            'Description',
        ];
    }

    public function map($ticket): array
    {
        return [
            substr($ticket->id, 0, 8) . '...',
            $ticket->title,
            ucfirst($ticket->status),
            ucfirst($ticket->priority),
            $ticket->customer_name ?? 'N/A',
            $ticket->customer_email ?? 'N/A',
            $ticket->customer_phone ?? 'N/A',
            $ticket->technician_name ?? 'Not Assigned',
            $ticket->technician_email ?? 'N/A',
            $ticket->district ?? 'N/A',
            $ticket->city ?? 'N/A',
            $ticket->gramsewa_division ?? 'N/A',
            $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : 'N/A',
            $ticket->accepted_at ? date('Y-m-d H:i:s', strtotime($ticket->accepted_at)) : 'N/A',
            $ticket->completed_at ? date('Y-m-d H:i:s', strtotime($ticket->completed_at)) : 'N/A',
            $ticket->description ?? 'N/A',
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
            'B' => 30,
            'C' => 15,
            'D' => 12,
            'E' => 25,
            'F' => 30,
            'G' => 15,
            'H' => 25,
            'I' => 30,
            'J' => 20,
            'K' => 20,
            'L' => 25,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 40,
        ];
    }

    public function title(): string
    {
        return 'Tickets';
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('tickets.title', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.description', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.district', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.city', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('tickets.status', $this->filters['status']);
        }

        if (!empty($this->filters['priority'])) {
            $query->where('tickets.priority', $this->filters['priority']);
        }

        if (!empty($this->filters['assigned_to'])) {
            $query->where('tickets.assigned_to', $this->filters['assigned_to']);
        }

        if (!empty($this->filters['district'])) {
            $query->where('tickets.district', $this->filters['district']);
        }

        if (!empty($this->filters['city'])) {
            $query->where('tickets.city', $this->filters['city']);
        }

        if (!empty($this->filters['gramsewa_division'])) {
            $query->where('tickets.gramsewa_division', $this->filters['gramsewa_division']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('tickets.created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('tickets.created_at', '<=', $this->filters['end_date']);
        }
    }
}
