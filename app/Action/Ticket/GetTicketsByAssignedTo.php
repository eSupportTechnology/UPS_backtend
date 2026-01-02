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
                    'tickets.customer_id',
                    'tickets.branch_id',
                    'tickets.title',
                    'tickets.description',
                    'tickets.address',
                    'tickets.photo_paths',
                    'tickets.status',
                    'tickets.priority',
                    'tickets.district',
                    'tickets.city',
                    'tickets.gramsewa_division',
                    'tickets.accepted_at',
                    'tickets.completed_at',
                    'tickets.created_at',
                ])
                ->join('users', 'tickets.customer_id', '=', 'users.id')
                ->leftJoin('company_branches', 'tickets.branch_id', '=', 'company_branches.id')
                ->addSelect([
                    'users.name as customer_name',
                    'users.email as customer_email',
                    'users.phone as customer_phone',
                    'users.customer_type',
                    'company_branches.branch_name',
                    'company_branches.is_primary',
                ])
                ->where('tickets.assigned_to', $assignedTo);

            $this->applyFilters($query, $filters);
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

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('tickets.title', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.description', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.district', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.city', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.gramsewa_division', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['district'])) {
            $query->where('tickets.district', $filters['district']);
        }

        if (!empty($filters['city'])) {
            $query->where('tickets.city', $filters['city']);
        }

        if (!empty($filters['gramsewa_division'])) {
            $query->where('tickets.gramsewa_division', $filters['gramsewa_division']);
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $sortableColumns = [
            'created_at' => 'tickets.created_at',
            'status' => 'tickets.status',
            'priority' => 'tickets.priority',
            'accepted_at' => 'tickets.accepted_at',
            'completed_at' => 'tickets.completed_at',
            'district' => 'tickets.district',
            'city' => 'tickets.city',
            'gramsewa_division' => 'tickets.gramsewa_division',
        ];

        $qualifiedSortBy = $sortableColumns[$sortBy] ?? 'tickets.created_at';

        $query->orderBy($qualifiedSortBy, $sortDirection);

        if ($sortBy !== 'id') {
            $query->orderBy('tickets.id', 'desc');
        }
    }
}
