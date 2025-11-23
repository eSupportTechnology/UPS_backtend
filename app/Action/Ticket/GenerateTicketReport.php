<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateTicketReport
{
    public function __invoke(array $filters = []): array
    {
        try {
            $tickets = $this->getTickets($filters);

            $statistics = [
                'total_tickets' => $tickets->count(),
                'pending_count' => $tickets->where('status', 'pending')->count(),
                'assigned_count' => $tickets->where('status', 'assigned')->count(),
                'accepted_count' => $tickets->where('status', 'accepted')->count(),
                'in_progress_count' => $tickets->where('status', 'in_progress')->count(),
                'completed_count' => $tickets->where('status', 'completed')->count(),
                'cancelled_count' => $tickets->where('status', 'cancelled')->count(),
                'low_priority_count' => $tickets->where('priority', 'low')->count(),
                'medium_priority_count' => $tickets->where('priority', 'medium')->count(),
                'high_priority_count' => $tickets->where('priority', 'high')->count(),
                'urgent_priority_count' => $tickets->where('priority', 'urgent')->count(),
            ];

            $ticketsByStatus = $tickets->groupBy('status')->map(function($group) {
                return $group->count();
            });

            $ticketsByPriority = $tickets->groupBy('priority')->map(function($group) {
                return $group->count();
            });

            $ticketsByDistrict = $tickets->groupBy('district')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'completed' => $group->where('status', 'completed')->count(),
                ];
            });

            $ticketsByCity = $tickets->groupBy('city')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'completed' => $group->where('status', 'completed')->count(),
                ];
            });

            $monthlyTrend = $this->getMonthlyTrend($filters);

            $report = [
                'statistics' => $statistics,
                'tickets_by_status' => $ticketsByStatus,
                'tickets_by_priority' => $ticketsByPriority,
                'tickets_by_district' => $ticketsByDistrict,
                'tickets_by_city' => $ticketsByCity,
                'monthly_trend' => $monthlyTrend,
                'tickets' => $tickets->map(function($ticket) {
                    $duration = null;
                    if ($ticket->completed_at) {
                        $start = \Carbon\Carbon::parse($ticket->created_at);
                        $end = \Carbon\Carbon::parse($ticket->completed_at);
                        $duration = $start->diffInDays($end);
                    }

                    return [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'customer_name' => $ticket->customer_name,
                        'customer_email' => $ticket->customer_email,
                        'technician_name' => $ticket->technician_name,
                        'district' => $ticket->district,
                        'city' => $ticket->city,
                        'gramsewa_division' => $ticket->gramsewa_division,
                        'created_at' => $ticket->created_at,
                        'accepted_at' => $ticket->accepted_at,
                        'completed_at' => $ticket->completed_at,
                        'duration_days' => $duration,
                    ];
                }),
                'generated_at' => now()->toDateTimeString(),
            ];

            return CommonResponse::sendSuccessResponseWithData('report', $report);
        } catch (\Exception $e) {
            Log::error('Failed to generate ticket report: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            return CommonResponse::sendBadResponseWithMessage('Error generating report');
        }
    }

    private function getTickets(array $filters)
    {
        $query = Ticket::select([
            'tickets.id',
            'tickets.customer_id',
            'tickets.title',
            'tickets.description',
            'tickets.status',
            'tickets.priority',
            'tickets.assigned_to',
            'tickets.district',
            'tickets.city',
            'tickets.gramsewa_division',
            'tickets.accepted_at',
            'tickets.completed_at',
            'tickets.created_at',
        ])
            ->join('users', 'tickets.customer_id', '=', 'users.id')
            ->leftJoin('users as technicians', 'tickets.assigned_to', '=', 'technicians.id')
            ->addSelect([
                'users.name as customer_name',
                'users.email as customer_email',
                'technicians.name as technician_name',
            ]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('tickets.created_at', 'desc')->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('tickets.title', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.description', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.district', 'LIKE', "%{$search}%")
                    ->orWhere('tickets.city', 'LIKE', "%{$search}%");
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

        if (!empty($filters['district'])) {
            $query->where('tickets.district', $filters['district']);
        }

        if (!empty($filters['city'])) {
            $query->where('tickets.city', $filters['city']);
        }

        if (!empty($filters['gramsewa_division'])) {
            $query->where('tickets.gramsewa_division', $filters['gramsewa_division']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('tickets.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tickets.created_at', '<=', $filters['end_date']);
        }
    }

    private function getMonthlyTrend(array $filters): array
    {
        $query = Ticket::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
            DB::raw('SUM(CASE WHEN priority = "urgent" THEN 1 ELSE 0 END) as urgent')
        )->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12);

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->toArray();
    }
}
