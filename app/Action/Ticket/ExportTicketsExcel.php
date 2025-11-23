<?php

namespace App\Action\Ticket;

use App\Exports\TicketsExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportTicketsExcel
{
    public function __invoke(array $filters = []): BinaryFileResponse
    {
        try {
            $fileName = 'tickets_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(
                new TicketsExport($filters),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Failed to export tickets to Excel: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
