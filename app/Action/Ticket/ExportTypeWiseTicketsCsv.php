<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTypeWiseTicketsCsv
{
    public function __invoke(string $type): StreamedResponse
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
            ])
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->leftJoin('company_branches', 'tickets.branch_id', '=', 'company_branches.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.customer_type',
                    'company_branches.name as branch_name',
                ])
                ->where('users.customer_type', $type)
                ->orderBy('tickets.created_at', 'desc')
                ->get();

            $fileName = 'tickets-' . $type . '-' . now()->format('Y_m_d_His') . '.csv';

            $response = new StreamedResponse(function() use ($tickets, $type) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['TICKET REPORT - ' . strtoupper($type) . ' CUSTOMERS']);
                fputcsv($handle, ['Total Tickets: ' . $tickets->count()]);
                fputcsv($handle, []);

                $locationHeader = $type === 'company' ? 'Branch' : 'Address';
                fputcsv($handle, ['ID', 'Title', 'Customer', 'Status', 'Priority', 'Created', $locationHeader]);

                foreach ($tickets as $ticket) {
                    $location = $type === 'company'
                        ? ($ticket->branch_name ?? '-')
                        : ($ticket->address ?? '-');

                    fputcsv($handle, [
                        $ticket->id,
                        $ticket->title ?? '-',
                        $ticket->customer_name ?? '-',
                        strtoupper($ticket->status ?? 'Open'),
                        strtoupper($ticket->priority ?? 'Medium'),
                        $this->formatDate($ticket->created_at),
                        $location,
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to export type-wise tickets to CSV: ' . $e->getMessage(), [
                'type' => $type,
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
