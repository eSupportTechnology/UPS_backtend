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
                'tickets.id',
                'tickets.customer_id',
                'tickets.title',
                'tickets.description',
                'tickets.photo_paths',
                'tickets.status',
                'tickets.priority',
                'tickets.assigned_to',
                'tickets.accepted_at',
                'tickets.completed_at',
            ])
                ->leftJoin('users', 'tickets.assigned_to', '=', 'users.id')
                ->addSelect([
                    'users.name as technician_name',
                    'users.email as technician_email',
                    'users.phone as technician_phone',
                    'users.address as technician_address',
                ]);
            $query->where('tickets.customer_id', $filters['customer_id']);

            $perPage = $filters['per_page'] ?? 10;

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';

            $query->orderBy($sortBy, $sortDirection);
            if ($sortBy !== 'id') {
                $query->orderBy('tickets.id', 'desc');
            }

            $tickets = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('tickets', $tickets);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets by customer: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving tickets for customer');
        }
    }
}
