<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetTicketsByAssignedTo
{
    public function __invoke(string $assignedTo, array $filters = []): array
    {
        try {
            $query = Ticket::with('assignedTechnician')
                ->select([
                    'tickets.id',
                    'tickets.title',
                    'tickets.description',
                    'tickets.photo_paths',
                    'tickets.status',
                    'tickets.priority',
                ])
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.email as customer_email',
                    'users.phone as customer_phone',
                    'users.address as customer_address',
                ])
                ->where('tickets.assigned_to', $assignedTo);

            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;
            $tickets = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('tickets', $tickets);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets by assigned technician: ' . $e->getMessage(), [
                'assigned_to' => $assignedTo,
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving assigned tickets');
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'id') {
            $query->orderBy('tickets.id', 'desc');
        }
    }
}
