<?php

namespace App\Http\Controllers;

use App\Models\ShopInventory;
use App\Models\Ticket;
use App\Models\AmcContract;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function ticketReport(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        $query = Ticket::query();

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $data = $query->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        return response()->json($data);
    }

    public function inventoryStockReport(): JsonResponse
    {
        $stock = ShopInventory::select('id', 'product_name', 'serial_number', 'quantity', 'purchase_date','unit_price')
            ->orderBy('product_name')
            ->get();

        return response()->json($stock);
    }

    public function warrantyExpiryReport(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = $filters['days'] ?? 30;
        $to = Carbon::now()->addDays($days);

        // assumes Ticket or Product model has warranty_expiry_date
        $items = Ticket::whereNotNull('warranty_expiry_date')
            ->whereBetween('warranty_expiry_date', [Carbon::now(), $to])
            ->get();

        return response()->json($items);
    }

    public function amcExpiryReport(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = $filters['days'] ?? 30;
        $to = Carbon::now()->addDays($days);

        $contracts = AmcContract::whereBetween('end_date', [Carbon::now(), $to])->get();

        return response()->json($contracts);
    }

    public function operatorActivityReport(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        $query = Ticket::query()
            ->whereHas('operator', function ($q) {
                $q->where('role', 'operator');
            });

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $data = $query->selectRaw('operator_id, COUNT(*) as assigned_tickets')
            ->groupBy('operator_id')
            ->with('operator:id,name,email')
            ->get();

        return response()->json($data);
    }
}
