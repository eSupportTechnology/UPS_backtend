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
                'tickets.branch_id',
                'tickets.title',
                'tickets.description',
                'tickets.address',
                'tickets.photo_paths',
                'tickets.status',
                'tickets.priority',
                'tickets.assigned_to',
                'tickets.accepted_at',
                'tickets.completed_at',
                'tickets.created_at',
            ])
                ->join('users as customers', 'tickets.customer_id', '=', 'customers.id')
                ->leftJoin('users as technicians', 'tickets.assigned_to', '=', 'technicians.id')
                ->leftJoin('company_branches', 'tickets.branch_id', '=', 'company_branches.id')
                ->addSelect([
                    'customers.customer_type',
                    'company_branches.branch_name',
                    'company_branches.is_primary',
                    'technicians.name as technician_name',
                    'technicians.email as technician_email',
                    'technicians.phone as technician_phone',
                ]);
            $query->where('tickets.customer_id', $filters['customer_id']);

            $perPage = $filters['per_page'] ?? 10;

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';

            $sortByColumn = $sortBy === 'created_at' ? 'tickets.created_at' : 'tickets.' . $sortBy;

            $query->orderBy($sortByColumn, $sortDirection);

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
