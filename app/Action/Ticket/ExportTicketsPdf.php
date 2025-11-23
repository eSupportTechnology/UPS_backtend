<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportTicketsPdf
{
    public function __invoke(array $filters = []): Response
    {
        try {
            $tickets = $this->getTickets($filters);

            $data = [
                'tickets' => $tickets,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_tickets' => $tickets->count(),
                'pending_count' => $tickets->where('status', 'pending')->count(),
                'assigned_count' => $tickets->where('status', 'assigned')->count(),
                'accepted_count' => $tickets->where('status', 'accepted')->count(),
                'in_progress_count' => $tickets->where('status', 'in_progress')->count(),
                'completed_count' => $tickets->where('status', 'completed')->count(),
                'cancelled_count' => $tickets->where('status', 'cancelled')->count(),
                'high_priority_count' => $tickets->where('priority', 'high')->count(),
                'urgent_priority_count' => $tickets->where('priority', 'urgent')->count(),
            ];

            $pdf = Pdf::loadView('reports.tickets', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'tickets_' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export tickets to PDF: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getTickets(array $filters)
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
                'technicians.name as technician_name',
            ]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('tickets.created_at', 'desc')->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('tickets.title', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.description', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.district', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.city', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('tickets.status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('tickets.priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('tickets.assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['district'])) {
            $query->where('tickets.district', $filters['district']);
        }

        if (!empty($filters['city'])) {
            $query->where('tickets.city', $filters['city']);
        }

        if (!empty($filters['gramsewa_division'])) {
            $query->where('tickets.gramsewa_division', $filters['gramsewa_division']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('tickets.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tickets.created_at', '<=', $filters['end_date']);
        }
    }
}
