<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportBulkTicketsPdf
{
    public function __invoke(): Response
    {
        try {
            $tickets = Ticket::select([
                'tickets.id',
                'tickets.customer_id',
                'tickets.title',
                'tickets.description',
                'tickets.status',
                'tickets.priority',
                'tickets.assigned_to',
                'tickets.address',
                'tickets.branch_id',
                'tickets.created_at',
                'tickets.completed_at',
            ])
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->leftJoin('company_branches', 'tickets.branch_id', '=', 'company_branches.id')
                ->leftJoin('users as technicians', 'tickets.assigned_to', '=', 'technicians.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.email as customer_email',
                    'users.phone as customer_phone',
                    'users.customer_type',
                    'company_branches.name as branch_name',
                    'company_branches.is_primary',
                    'technicians.name as technician_name',
                ])
                ->orderBy('tickets.created_at', 'desc')
                ->get();

            $data = [
                'tickets' => $tickets,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_tickets' => $tickets->count(),
                'personal_count' => $tickets->where('customer_type', 'personal')->count(),
                'company_count' => $tickets->where('customer_type', 'company')->count(),
                'status_breakdown' => $this->getStatusBreakdown($tickets),
                'priority_breakdown' => $this->getPriorityBreakdown($tickets),
            ];

            $pdf = Pdf::loadView('reports.bulk-tickets', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'tickets-bulk-export-' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export bulk tickets to PDF: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getStatusBreakdown($tickets)
    {
        return [
            'open' => $tickets->where('status', 'open')->count(),
            'assigned' => $tickets->where('status', 'assigned')->count(),
            'accepted' => $tickets->where('status', 'accepted')->count(),
            'in_progress' => $tickets->where('status', 'in_progress')->count(),
            'completed' => $tickets->where('status', 'completed')->count(),
            'cancelled' => $tickets->where('status', 'cancelled')->count(),
        ];
    }

    private function getPriorityBreakdown($tickets)
    {
        return [
            'low' => $tickets->where('priority', 'low')->count(),
            'medium' => $tickets->where('priority', 'medium')->count(),
            'high' => $tickets->where('priority', 'high')->count(),
            'urgent' => $tickets->where('priority', 'urgent')->count(),
        ];
    }
}
