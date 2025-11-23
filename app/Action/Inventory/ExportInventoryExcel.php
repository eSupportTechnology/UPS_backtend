<?php

namespace App\Action\Inventory;

use App\Exports\ShopInventoriesExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportInventoryExcel
{
    public function __invoke(array $filters = []): BinaryFileResponse
    {
        try {
            $fileName = 'shop_inventories_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(
                new ShopInventoriesExport($filters),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Failed to export shop inventories to Excel: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
