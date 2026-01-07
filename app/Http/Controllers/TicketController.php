<?php

namespace App\Http\Controllers;

use App\Action\Ticket\AcceptTicket;
use App\Action\Ticket\AssignTicket;
use App\Action\Ticket\CompleteTicket;
use App\Action\Ticket\CreateTicket;
use App\Action\Ticket\GetAllTickets;
use App\Action\Ticket\GetTicketById;
use App\Action\Ticket\GetTicketsByAssignedTo;
use App\Action\Ticket\GetTicketsByCustomer;
use App\Action\Ticket\ExportTicketsExcel;
use App\Action\Ticket\ExportTicketsPdf;
use App\Action\Ticket\GenerateTicketReport;
use App\Action\Ticket\ConvertToInsideJob;
use App\Action\Ticket\CreateInsideJobDirect;
use App\Action\Ticket\InspectInsideJob;
use App\Action\Ticket\CreateQuote;
use App\Action\Ticket\ApproveQuote;
use App\Action\Ticket\StartRepair;
use App\Action\Ticket\CompleteInsideJob;
use App\Action\Ticket\ExportBulkTicketsPdf;
use App\Action\Ticket\ExportBulkTicketsCsv;
use App\Action\Ticket\ExportDateRangeTicketsCsv;
use App\Action\Ticket\ExportTypeWiseTicketsCsv;
use App\Action\Ticket\ExportStatusWiseTicketsCsv;
use App\Action\Ticket\ExportPriorityWiseTicketsCsv;
use App\Action\Ticket\ExportInsideJobsPdf;
use App\Action\Ticket\ExportInsideJobsExcel;
use App\Action\Ticket\ExportMaterialsPdf;
use App\Action\Ticket\ExportMaterialsExcel;
use App\Http\Requests\Ticket\AcceptTicketRequest;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\CompleteTicketRequest;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketsByAssignedToRequest;
use App\Http\Requests\Ticket\GetTicketsByCustomerRequest;
use App\Http\Requests\Ticket\TicketRequest;
use App\Http\Requests\Ticket\TicketReportRequest;
use App\Http\Requests\Ticket\ConvertToInsideJobRequest;
use App\Http\Requests\Ticket\CreateInsideJobDirectRequest;
use App\Http\Requests\Ticket\InspectInsideJobRequest;
use App\Http\Requests\Ticket\CreateQuoteRequest;
use App\Http\Requests\Ticket\ApproveQuoteRequest;
use App\Http\Requests\Ticket\StartRepairRequest;
use App\Http\Requests\Ticket\CompleteInsideJobRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function createTicket(TicketRequest $request, CreateTicket $createTicket): JsonResponse
    {
        return response()->json($createTicket($request->validated()));
    }

    public function getTicketById(string $ticketId, GetTicketById $getTicketById): JsonResponse
    {
        $result = $getTicketById($ticketId);
        return response()->json($result);
    }

    public function getAllTickets(GetAllTicketsRequest $request, GetAllTickets $getAllTickets): JsonResponse
    {
        $result = $getAllTickets($request->validated());
        return response()->json($result);
    }

    public function getTicketsByCustomer(GetTicketsByCustomerRequest $request, GetTicketsByCustomer $action): JsonResponse
    {
        $filters = $request->validated();
        $result = $action($filters);
        return response()->json($result);
    }

    public function assignTicket(AssignTicketRequest $request, AssignTicket $assignTicket): JsonResponse
    {
        return response()->json($assignTicket($request->validated()));
    }

    public function acceptTicket(AcceptTicketRequest $request, AcceptTicket $acceptTicket): JsonResponse
    {
        return response()->json($acceptTicket($request->validated()));
    }

    public function completeTicket(CompleteTicketRequest $request, CompleteTicket $completeTicket): JsonResponse
    {
        return response()->json($completeTicket($request->validated()));
    }

    public function getTicketsByAssignedTo(
        string $assigned_to,
        GetTicketsByAssignedToRequest $request,
        GetTicketsByAssignedTo $getTicketsByAssignedTo
    ): JsonResponse {
        $result = $getTicketsByAssignedTo($assigned_to, $request->validated());
        return response()->json($result);
    }

    public function exportExcel(TicketReportRequest $request, ExportTicketsExcel $action): BinaryFileResponse
    {
        return $action($request->validated());
    }

    public function exportPdf(TicketReportRequest $request, ExportTicketsPdf $action): Response
    {
        return $action($request->validated());
    }

    public function generateReport(TicketReportRequest $request, GenerateTicketReport $action): JsonResponse
    {
        return response()->json($action($request->validated()));
    }

    // Inside Job Workflow Methods
    public function convertToInsideJob(
        ConvertToInsideJobRequest $request,
        ConvertToInsideJob $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function createInsideJobDirect(
        CreateInsideJobDirectRequest $request,
        CreateInsideJobDirect $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function inspectInsideJob(
        InspectInsideJobRequest $request,
        InspectInsideJob $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function createQuote(
        CreateQuoteRequest $request,
        CreateQuote $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function approveQuote(
        ApproveQuoteRequest $request,
        ApproveQuote $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function startRepair(
        StartRepairRequest $request,
        StartRepair $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function completeInsideJob(
        CompleteInsideJobRequest $request,
        CompleteInsideJob $action
    ): JsonResponse {
        return response()->json($action($request->validated()));
    }

    public function updateInsideJobStatus(): JsonResponse
    {
        try {
            $ticket_id = request()->input('ticket_id');
            $status = request()->input('status');

            if (!$ticket_id || !$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ticket_id, status'
                ], 400);
            }

            $ticket = Ticket::findOrFail($ticket_id);

            // Validate status
            $validStatuses = ['pending_inspection', 'in_repair', 'completed', 'quote_rejected', 'inspected', 'quoted', 'approved_for_repair'];
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 400);
            }

            // If completing, also clear planned materials
            if ($status === 'completed') {
                $ticket->update([
                    'status' => $status,
                    'completed_at' => now(),
                    'planned_materials' => null,
                ]);

                // Delete planned materials from the job_planned_materials table
                \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)->delete();
            } else {
                $ticket->update(['status' => $status]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectInsideJob(): JsonResponse
    {
        try {
            $ticket_id = request()->input('ticket_id');
            $reason = request()->input('reason');
            $rollback_material_ids = request()->input('rollback_material_ids', []);

            if (!$ticket_id || !$reason) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ticket_id, reason'
                ], 400);
            }

            $ticket = Ticket::findOrFail($ticket_id);

            // Rollback selected materials to inventory
            if (!empty($rollback_material_ids)) {
                $materials = \App\Models\JobPlannedMaterial::whereIn('id', $rollback_material_ids)
                    ->where('ticket_id', $ticket_id)
                    ->get();

                foreach ($materials as $material) {
                    // Restore inventory quantity
                    $inventory = \App\Models\ShopInventory::find($material->inventory_id);
                    if ($inventory) {
                        $inventory->update(['quantity' => $inventory->quantity + $material->quantity]);
                    }
                    // Delete the planned material
                    $material->delete();
                }
            }

            // Delete remaining planned materials without rollback
            \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)->delete();

            // Update ticket status to rejected
            $ticket->update([
                'status' => 'quote_rejected',
                'rejection_reason' => $reason,
                'rejected_at' => now(),
                'planned_materials' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job rejected successfully',
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject job: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInsideJobs(): JsonResponse
    {
        $query = Ticket::insideJobs()
            ->with(['customer', 'assignedTechnician', 'inspector', 'quoter', 'quoteLineItems', 'plannedMaterials']);

        // Search filter (job number, customer name, UPS serial)
        if ($search = request()->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('ups_serial_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter (single or multiple comma-separated)
        if ($status = request()->query('status')) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        }

        // Priority filter
        if ($priority = request()->query('priority')) {
            $query->where('priority', $priority);
        }

        // Technician filter
        if ($technician = request()->query('technician')) {
            $query->where('assigned_to', $technician);
        }

        // Date range filter
        if ($fromDate = request()->query('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = request()->query('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Today only filter
        if (request()->query('today') === 'true') {
            $query->whereDate('created_at', now()->toDateString());
        }

        // Sorting
        $sortBy = request()->query('sort_by', 'created_at');
        $sortOrder = request()->query('sort_order', 'desc');
        $allowedSorts = ['created_at', 'job_number', 'priority', 'status', 'customer_name'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min((int) request()->query('per_page', 15), 100);
        $insideJobs = $query->paginate($perPage);

        // Add summary counts for dashboard
        $statusCounts = Ticket::insideJobs()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'success' => true,
            'data' => $insideJobs,
            'status_counts' => $statusCounts,
            'filters_applied' => [
                'search' => request()->query('search'),
                'status' => request()->query('status'),
                'priority' => request()->query('priority'),
                'technician' => request()->query('technician'),
                'from_date' => request()->query('from_date'),
                'to_date' => request()->query('to_date'),
            ]
        ]);
    }

    public function getJobCard(string $ticket_id): JsonResponse
    {
        try {
            $ticket = Ticket::with([
                'customer',
                'assignedTechnician',
                'inspector',
                'quoter',
                'quoteLineItems',
                'parentTicket'
            ])->findOrFail($ticket_id);

            if ($ticket->job_type !== 'inside') {
                return response()->json([
                    'success' => false,
                    'message' => 'Not an inside job'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }
    }

    public function addPlannedMaterial(): JsonResponse
    {
        try {
            $ticket_id = request()->input('ticket_id');
            $inventory_id = request()->input('inventory_id');
            $product_name = request()->input('product_name');
            $brand = request()->input('brand');
            $category = request()->input('category');
            $quantity = request()->input('quantity', 1);

            if (!$ticket_id || !$inventory_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ticket_id, inventory_id'
                ], 400);
            }

            $ticket = Ticket::findOrFail($ticket_id);
            $inventory = \App\Models\ShopInventory::findOrFail($inventory_id);

            // Check stock
            if ($inventory->quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$product_name}. Available: {$inventory->quantity}"
                ], 400);
            }

            // Check if material already exists for this job
            $existing = \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)
                ->where('inventory_id', $inventory_id)
                ->first();

            if ($existing) {
                // Update quantity
                $newQty = $existing->quantity + $quantity;
                if ($inventory->quantity < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product_name}. Available: {$inventory->quantity}"
                    ], 400);
                }
                $existing->update(['quantity' => $newQty]);
            } else {
                // Create new entry
                \App\Models\JobPlannedMaterial::create([
                    'ticket_id' => $ticket_id,
                    'inventory_id' => $inventory_id,
                    'product_name' => $product_name,
                    'brand' => $brand,
                    'category' => $category,
                    'quantity' => $quantity,
                ]);
            }

            // Decrease inventory
            $inventory->update(['quantity' => $inventory->quantity - $quantity]);

            // Get updated materials list
            $materials = \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Material added successfully',
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removePlannedMaterial(): JsonResponse
    {
        try {
            $material_id = request()->input('material_id');

            if (!$material_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required field: material_id'
                ], 400);
            }

            $material = \App\Models\JobPlannedMaterial::findOrFail($material_id);
            $ticket_id = $material->ticket_id;

            // Restore inventory
            $inventory = \App\Models\ShopInventory::find($material->inventory_id);
            if ($inventory) {
                $inventory->update(['quantity' => $inventory->quantity + $material->quantity]);
            }

            // Delete material
            $material->delete();

            // Get updated materials list
            $materials = \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Material removed successfully',
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPlannedMaterials(string $ticket_id): JsonResponse
    {
        try {
            $materials = \App\Models\JobPlannedMaterial::where('ticket_id', $ticket_id)->get();

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get materials: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePlannedMaterialQuantity(): JsonResponse
    {
        try {
            $material_id = request()->input('material_id');
            $quantity = request()->input('quantity');

            if (!$material_id || !$quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: material_id, quantity'
                ], 400);
            }

            $material = \App\Models\JobPlannedMaterial::findOrFail($material_id);
            $inventory = \App\Models\ShopInventory::findOrFail($material->inventory_id);

            $oldQuantity = $material->quantity;
            $diff = $quantity - $oldQuantity;

            if ($diff > 0) {
                // Need more from inventory
                if ($inventory->quantity < $diff) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. Available: {$inventory->quantity}"
                    ], 400);
                }
                $inventory->update(['quantity' => $inventory->quantity - $diff]);
            } else {
                // Return to inventory
                $inventory->update(['quantity' => $inventory->quantity + abs($diff)]);
            }

            $material->update(['quantity' => $quantity]);

            // Get updated materials list
            $materials = \App\Models\JobPlannedMaterial::where('ticket_id', $material->ticket_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully',
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    // Advanced Export Endpoints
    public function exportBulkTicketsPdf(ExportBulkTicketsPdf $action): Response
    {
        return $action();
    }

    public function exportBulkTicketsCsv(ExportBulkTicketsCsv $action): StreamedResponse
    {
        return $action();
    }

    public function exportDateRangeTicketsCsv(ExportDateRangeTicketsCsv $action): StreamedResponse
    {
        $fromDate = request()->query('from_date');
        $toDate = request()->query('to_date');

        if (!$fromDate || !$toDate) {
            abort(400, 'Missing required parameters: from_date and to_date');
        }

        return $action($fromDate, $toDate);
    }

    public function exportTypeWiseTicketsCsv(ExportTypeWiseTicketsCsv $action): StreamedResponse
    {
        $type = request()->query('type');

        if (!$type || !in_array($type, ['personal', 'company'])) {
            abort(400, 'Invalid or missing type parameter');
        }

        return $action($type);
    }

    public function exportStatusWiseTicketsCsv(ExportStatusWiseTicketsCsv $action): StreamedResponse
    {
        $status = request()->query('status');

        if (!$status) {
            abort(400, 'Missing required parameter: status');
        }

        return $action($status);
    }

    public function exportPriorityWiseTicketsCsv(ExportPriorityWiseTicketsCsv $action): StreamedResponse
    {
        $priority = request()->query('priority');

        if (!$priority) {
            abort(400, 'Missing required parameter: priority');
        }

        return $action($priority);
    }

    // Inside Jobs Export Endpoints
    public function exportInsideJobsPdf(ExportInsideJobsPdf $action): Response
    {
        $filters = [
            'search' => request()->query('search'),
            'status' => request()->query('status'),
            'priority' => request()->query('priority'),
            'technician' => request()->query('technician'),
            'from_date' => request()->query('from_date'),
            'to_date' => request()->query('to_date'),
            'today' => request()->query('today'),
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        return $action($filters);
    }

    public function exportInsideJobsExcel(ExportInsideJobsExcel $action): BinaryFileResponse
    {
        $filters = [
            'search' => request()->query('search'),
            'status' => request()->query('status'),
            'priority' => request()->query('priority'),
            'technician' => request()->query('technician'),
            'from_date' => request()->query('from_date'),
            'to_date' => request()->query('to_date'),
            'today' => request()->query('today'),
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        return $action($filters);
    }

    // Materials Export Endpoints
    public function exportMaterialsPdf(ExportMaterialsPdf $action): Response
    {
        $filters = [
            'search' => request()->query('search'),
            'category' => request()->query('category'),
            'brand' => request()->query('brand'),
            'job_id' => request()->query('job_id'),
            'status' => request()->query('status'),
            'from_date' => request()->query('from_date'),
            'to_date' => request()->query('to_date'),
            'today' => request()->query('today'),
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        return $action($filters);
    }

    public function exportMaterialsExcel(ExportMaterialsExcel $action): BinaryFileResponse
    {
        $filters = [
            'search' => request()->query('search'),
            'category' => request()->query('category'),
            'brand' => request()->query('brand'),
            'job_id' => request()->query('job_id'),
            'status' => request()->query('status'),
            'from_date' => request()->query('from_date'),
            'to_date' => request()->query('to_date'),
            'today' => request()->query('today'),
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        return $action($filters);
    }
}
