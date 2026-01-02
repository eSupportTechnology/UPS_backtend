<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportStatusWiseTicketsCsv
{
    public function __invoke(string $status): StreamedResponse
    {
        try {
            $tickets = Ticket::select([
                'tickets.id',
                'tickets.customer_id',
                'tickets.title',
                'tickets.description',
                'tickets.status',
                'tickets.priority',
                'tickets.created_at',
                'tickets.completed_at',
            ])
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->leftJoin('users as technicians', 'tickets.assigned_to', '=', 'technicians.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.customer_type',
                    'technicians.name as technician_name',
                ])
                ->where('tickets.status', $status)
                ->orderBy('tickets.created_at', 'desc')
                ->get();

            $fileName = 'tickets-' . $status . '-' . now()->format('Y_m_d_His') . '.csv';

            $response = new StreamedResponse(function() use ($tickets, $status) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['TICKET REPORT - ' . strtoupper($status) . ' STATUS']);
                fputcsv($handle, ['Total Tickets: ' . $tickets->count()]);
                fputcsv($handle, []);

                fputcsv($handle, ['ID', 'Title', 'Customer', 'Type', 'Priority', 'Created', 'Completed']);

                foreach ($tickets as $ticket) {
                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->title ?? '-',
                        $ticket->customer_name ?? '-',
                        strtoupper($ticket->customer_type ?? '-'),
                        strtoupper($ticket->priority ?? 'Medium'),
                        $this->formatDate($ticket->created_at),
                        $ticket->completed_at ? $this->formatDate($ticket->completed_at) : '-',
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to export status-wise tickets to CSV: ' . $e->getMessage(), [
                'status' => $status,
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
