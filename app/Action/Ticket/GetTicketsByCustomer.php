<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;

class GetTicketsByCustomer
{
    public function __invoke(array $filters = []): array
    {
        try {
            
            $query = Ticket::select([
                'id',
                'customer_id',
                'title',
                'description',
                'photo_paths',
                'status',
                'priority',
                'assigned_to',
                'accepted_at',
                'completed_at',
                'created_at',
                'updated_at',
            ]);
            $query->where('customer_id', $filters['customer_id']);

            // Apply search filter
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                      ->orWhere('description', 'LIKE', '%' . $search . '%')
                      ->orWhere('id', 'LIKE', '%' . $search . '%');
                });
            }

            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $perPage = $filters['per_page'] ?? 10;

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';

            
            $query->orderBy($sortBy, $sortDirection);
            if ($sortBy !== 'id') {
                $query->orderBy('id', 'desc');
            }

            $tickets = $query->paginate($perPage);

            return [
                'status' => 200,
                'message' => 'Tickets retrieved successfully',
                'data' => [
                    'tickets' => $tickets->items(),
                    'pagination' => [
                        'current_page' => $tickets->currentPage(),
                        'per_page' => $tickets->perPage(),
                        'total' => $tickets->total(),
                        'last_page' => $tickets->lastPage(),
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets by customer: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving tickets for customer');
        }
    }
}
