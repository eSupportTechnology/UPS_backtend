<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportBulkTicketsCsv
{
    public function __invoke(): StreamedResponse
    {
        try {
            $tickets = Ticket::select([
                'tickets.id',
                'tickets.customer_id',
                'tickets.title',
                'tickets.description',
                'tickets.status',
                'tickets.priority',
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
                    'technicians.name as technician_name',
                ])
                ->orderBy('tickets.created_at', 'desc')
                ->get();

            $fileName = 'tickets-bulk-export-' . now()->format('Y_m_d_His') . '.csv';

            $response = new StreamedResponse(function() use ($tickets) {
                $handle = fopen('php://output', 'w');

                // Header
                fputcsv($handle, ['TICKET EXPORT REPORT']);
                fputcsv($handle, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
                fputcsv($handle, ['Total Tickets: ' . $tickets->count()]);
                fputcsv($handle, []);

                // Summary Statistics
                fputcsv($handle, ['SUMMARY STATISTICS']);
                fputcsv($handle, ['Metric', 'Count', 'Percentage']);
                fputcsv($handle, ['Total', $tickets->count(), '100%']);
                fputcsv($handle, ['Personal Customers', $tickets->where('customer_type', 'personal')->count(),
                    number_format(($tickets->where('customer_type', 'personal')->count() / $tickets->count()) * 100, 1) . '%']);
                fputcsv($handle, ['Company Customers', $tickets->where('customer_type', 'company')->count(),
                    number_format(($tickets->where('customer_type', 'company')->count() / $tickets->count()) * 100, 1) . '%']);
                fputcsv($handle, []);

                // Status Breakdown
                fputcsv($handle, ['STATUS BREAKDOWN']);
                $statusCounts = [
                    'open' => $tickets->where('status', 'open')->count(),
                    'assigned' => $tickets->where('status', 'assigned')->count(),
                    'accepted' => $tickets->where('status', 'accepted')->count(),
                    'in_progress' => $tickets->where('status', 'in_progress')->count(),
                    'completed' => $tickets->where('status', 'completed')->count(),
                    'cancelled' => $tickets->where('status', 'cancelled')->count(),
                ];
                foreach ($statusCounts as $status => $count) {
                    fputcsv($handle, [ucfirst($status), $count]);
                }
                fputcsv($handle, []);

                // Priority Breakdown
                fputcsv($handle, ['PRIORITY BREAKDOWN']);
                $priorityCounts = [
                    'low' => $tickets->where('priority', 'low')->count(),
                    'medium' => $tickets->where('priority', 'medium')->count(),
                    'high' => $tickets->where('priority', 'high')->count(),
                    'urgent' => $tickets->where('priority', 'urgent')->count(),
                ];
                foreach ($priorityCounts as $priority => $count) {
                    fputcsv($handle, [ucfirst($priority), $count]);
                }
                fputcsv($handle, []);

                // Detailed Ticket List
                fputcsv($handle, ['DETAILED TICKET LIST']);
                fputcsv($handle, ['ID', 'Title', 'Customer', 'Type', 'Status', 'Priority', 'Created', 'Branch/Address']);

                foreach ($tickets as $ticket) {
                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->title ?? '-',
                        $ticket->customer_name ?? '-',
                        strtoupper($ticket->customer_type ?? '-'),
                        strtoupper($ticket->status ?? 'Open'),
                        strtoupper($ticket->priority ?? 'Medium'),
                        $this->formatDate($ticket->created_at),
                        $ticket->customer_type === 'company' ? ($ticket->branch_name ?? '-') : ($ticket->address ?? '-'),
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to export bulk tickets to CSV: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function formatDate($dateString)
    {
        if (!$dateString) return '-';
        return \Carbon\Carbon::parse($dateString)->format('Y-m-d H:i');
    }
}
