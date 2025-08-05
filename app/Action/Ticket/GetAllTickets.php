<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class GetAllTickets
{
    public function __invoke(array $filters = []): array
    {
        try {
            $query = Ticket::with('assignedTechnician')
            ->select([
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
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.email as customer_email',
                    'users.phone as customer_phone',
                    'users.address as customer_address',
                ]);

            $this->applyFilters($query, $filters);
            $this->applySorting($query, $filters);

            $perPage = $filters['per_page'] ?? 10;
            $tickets = $query->paginate($perPage);

            return CommonResponse::sendSuccessResponseWithData('tickets', $tickets);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error retrieving tickets list');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('tickets.title', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.description', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('tickets.status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('tickets.priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('tickets.assigned_to', $filters['assigned_to']);
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
