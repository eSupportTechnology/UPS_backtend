<?php

namespace App\Action\AMC;

use App\Exports\AMCContractsExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAMCContractsExcel
{
    public function __invoke(array $filters = []): BinaryFileResponse
    {
        try {
            $fileName = 'amc_contracts_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(
                new AMCContractsExport($filters),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Failed to export AMC contracts to Excel: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
