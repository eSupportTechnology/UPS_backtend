<?php

namespace App\Action\Ticket;

use App\Exports\InsideJobsExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportInsideJobsExcel
{
    public function __invoke(array $filters = []): BinaryFileResponse
    {
        try {
            $fileName = 'inside_jobs_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(
                new InsideJobsExport($filters),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Failed to export inside jobs to Excel: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
