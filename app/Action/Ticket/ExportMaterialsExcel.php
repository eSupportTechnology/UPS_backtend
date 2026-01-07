<?php

namespace App\Action\Ticket;

use App\Exports\MaterialsUsageExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportMaterialsExcel
{
    public function __invoke(array $filters = []): BinaryFileResponse
    {
        try {
            $fileName = 'materials_usage_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(
                new MaterialsUsageExport($filters),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Failed to export materials to Excel: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
