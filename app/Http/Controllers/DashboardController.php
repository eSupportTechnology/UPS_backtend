<?php

namespace App\Http\Controllers;

use App\Models\AMCContract;
use App\Models\AMCMaintenance;
use App\Models\Branch;
use App\Models\InventoryItemReturn;
use App\Models\InventoryItemUsage;
use App\Models\ShopInventory;
use App\Models\Ticket;
use App\Models\Track;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard
     */
    public function superAdminDashboard(): JsonResponse
    {
        try {
            $data = [
                // Users Statistics
                'users' => [
                    'total' => User::count(),
                    'super_admins' => User::where('role_as', User::ROLE_SUPER_ADMIN)->count(),
                    'admins' => User::where('role_as', User::ROLE_ADMIN)->count(),
                    'operators' => User::where('role_as', User::ROLE_OPERATOR)->count(),
                    'technicians' => User::where('role_as', User::ROLE_TECHNICIAN)->count(),
                    'customers' => User::where('role_as', User::ROLE_CUSTOMER)->count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'inactive_users' => User::where('is_active', false)->count(),
                ],

                // Branches Statistics
                'branches' => [
                    'total' => Branch::count(),
                    'active' => Branch::where('is_active', true)->count(),
                    'inactive' => Branch::where('is_active', false)->count(),
                    'by_type' => Branch::select('type', DB::raw('count(*) as count'))
                        ->groupBy('type')
                        ->get(),
                ],

                // Tickets Statistics
                'tickets' => [
                    'total' => Ticket::count(),
                    'pending' => Ticket::where('status', 'pending')->count(),
                    'in_progress' => Ticket::where('status', 'in_progress')->count(),
                    'completed' => Ticket::where('status', 'completed')->count(),
                    'cancelled' => Ticket::where('status', 'cancelled')->count(),
                    'by_priority' => Ticket::select('priority', DB::raw('count(*) as count'))
                        ->groupBy('priority')
                        ->get(),
                    'this_month' => Ticket::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count(),
                    'today' => Ticket::whereDate('created_at', Carbon::today())->count(),
                ],

                // AMC Contracts Statistics
                'amc_contracts' => [
                    'total' => AMCContract::count(),
                    'active' => AMCContract::where('is_active', true)->count(),
                    'inactive' => AMCContract::where('is_active', false)->count(),
                    'by_type' => AMCContract::select('contract_type', DB::raw('count(*) as count'))
                        ->groupBy('contract_type')
                        ->get(),
                    'expiring_soon' => AMCContract::where('is_active', true)
                        ->whereBetween('warranty_end_date', [Carbon::now(), Carbon::now()->addDays(30)])
                        ->count(),
                    'total_value' => AMCContract::where('is_active', true)
                        ->sum('contract_amount'),
                ],

                // AMC Maintenances Statistics
                'amc_maintenances' => [
                    'total' => AMCMaintenance::count(),
                    'pending' => AMCMaintenance::where('status', 'pending')->count(),
                    'in_progress' => AMCMaintenance::where('status', 'in_progress')->count(),
                    'completed' => AMCMaintenance::where('status', 'completed')->count(),
                    'scheduled_today' => AMCMaintenance::whereDate('scheduled_date', Carbon::today())->count(),
                    'scheduled_this_week' => AMCMaintenance::whereBetween('scheduled_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                    'overdue' => AMCMaintenance::where('status', 'pending')
                        ->where('scheduled_date', '<', Carbon::today())
                        ->count(),
                ],

                // Inventory Statistics
                'inventory' => [
                    'total_items' => ShopInventory::count(),
                    'total_quantity' => ShopInventory::sum('quantity'),
                    'total_value' => ShopInventory::selectRaw('SUM(quantity * unit_price) as total')
                        ->value('total'),
                    'low_stock' => ShopInventory::where('quantity', '<', 10)->count(),
                    'out_of_stock' => ShopInventory::where('quantity', 0)->count(),
                    'by_category' => ShopInventory::select('category', DB::raw('count(*) as count'))
                        ->groupBy('category')
                        ->get(),
                    'total_usage_this_month' => InventoryItemUsage::whereMonth('usage_date', Carbon::now()->month)
                        ->sum('quantity'),
                    'total_returns_this_month' => InventoryItemReturn::whereMonth('return_date', Carbon::now()->month)
                        ->sum('quantity'),
                ],

                // Tracking Statistics
                'tracking' => [
                    'active_tracks' => Track::whereNull('ended_at')->count(),
                    'total_tracks_today' => Track::whereDate('started_at', Carbon::today())->count(),
                    'total_tracks_this_month' => Track::whereMonth('started_at', Carbon::now()->month)
                        ->whereYear('started_at', Carbon::now()->year)
                        ->count(),
                    'active_technicians' => Track::whereNull('ended_at')
                        ->distinct('technician_id')
                        ->count('technician_id'),
                ],

                // Recent Activities
                'recent_activities' => [
                    'recent_tickets' => Ticket::with('assignedTechnician:id,name')
                        ->latest()
                        ->limit(5)
                        ->get(['id', 'title', 'status', 'priority', 'assigned_to', 'created_at']),
                    'recent_maintenances' => AMCMaintenance::with('assignedTechnician:id,name', 'amcContract:id')
                        ->latest()
                        ->limit(5)
                        ->get(['id', 'amc_contract_id', 'scheduled_date', 'status', 'assigned_to', 'created_at']),
                ],

                // Charts Data - Monthly Trends
                'monthly_trends' => [
                    'tickets' => Ticket::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                        ->where('created_at', '>=', Carbon::now()->subMonths(6))
                        ->groupBy('month')
                        ->orderBy('month')
                        ->get(),
                    'maintenances' => AMCMaintenance::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
                        ->where('created_at', '>=', Carbon::now()->subMonths(6))
                        ->groupBy('month')
                        ->orderBy('month')
                        ->get(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard(): JsonResponse
    {
        try {
            $data = [
                // Users Statistics (Limited)
                'users' => [
                    'operators' => User::where('role_as', User::ROLE_OPERATOR)->count(),
                    'technicians' => User::where('role_as', User::ROLE_TECHNICIAN)->count(),
                    'customers' => User::where('role_as', User::ROLE_CUSTOMER)->count(),
                    'active_users' => User::where('is_active', true)
                        ->whereIn('role_as', [User::ROLE_OPERATOR, User::ROLE_TECHNICIAN, User::ROLE_CUSTOMER])
                        ->count(),
                ],

                // Tickets Statistics
                'tickets' => [
                    'total' => Ticket::count(),
                    'pending' => Ticket::where('status', 'pending')->count(),
                    'in_progress' => Ticket::where('status', 'in_progress')->count(),
                    'completed' => Ticket::where('status', 'completed')->count(),
                    'unassigned' => Ticket::whereNull('assigned_to')->count(),
                    'by_priority' => Ticket::select('priority', DB::raw('count(*) as count'))
                        ->groupBy('priority')
                        ->get(),
                    'today' => Ticket::whereDate('created_at', Carbon::today())->count(),
                    'this_week' => Ticket::whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                    'this_month' => Ticket::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count(),
                ],

                // AMC Statistics
                'amc_contracts' => [
                    'total' => AMCContract::count(),
                    'active' => AMCContract::where('is_active', true)->count(),
                    'expiring_soon' => AMCContract::where('is_active', true)
                        ->whereBetween('warranty_end_date', [Carbon::now(), Carbon::now()->addDays(30)])
                        ->count(),
                    'total_value' => AMCContract::where('is_active', true)->sum('contract_amount'),
                ],

                'amc_maintenances' => [
                    'total' => AMCMaintenance::count(),
                    'pending' => AMCMaintenance::where('status', 'pending')->count(),
                    'in_progress' => AMCMaintenance::where('status', 'in_progress')->count(),
                    'completed' => AMCMaintenance::where('status', 'completed')->count(),
                    'scheduled_today' => AMCMaintenance::whereDate('scheduled_date', Carbon::today())->count(),
                    'scheduled_this_week' => AMCMaintenance::whereBetween('scheduled_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                    'overdue' => AMCMaintenance::where('status', 'pending')
                        ->where('scheduled_date', '<', Carbon::today())
                        ->count(),
                ],

                // Inventory Statistics
                'inventory' => [
                    'total_items' => ShopInventory::count(),
                    'low_stock' => ShopInventory::where('quantity', '<', 10)->count(),
                    'out_of_stock' => ShopInventory::where('quantity', 0)->count(),
                    'total_value' => ShopInventory::selectRaw('SUM(quantity * unit_price) as total')
                        ->value('total'),
                ],

                // Technician Performance
                'technician_performance' => [
                    'active_technicians' => Track::whereNull('ended_at')
                        ->distinct('technician_id')
                        ->count('technician_id'),
                    'top_technicians' => Ticket::select('assigned_to', DB::raw('count(*) as completed_tickets'))
                        ->where('status', 'completed')
                        ->whereNotNull('assigned_to')
                        ->groupBy('assigned_to')
                        ->orderByDesc('completed_tickets')
                        ->limit(5)
                        ->with('assignedTechnician:id,name')
                        ->get(),
                ],

                // Recent Activities
                'recent_activities' => [
                    'pending_tickets' => Ticket::with('assignedTechnician:id,name')
                        ->where('status', 'pending')
                        ->latest()
                        ->limit(10)
                        ->get(['id', 'title', 'status', 'priority', 'assigned_to', 'created_at']),
                    'upcoming_maintenances' => AMCMaintenance::with('assignedTechnician:id,name', 'amcContract:id')
                        ->where('status', 'pending')
                        ->where('scheduled_date', '>=', Carbon::today())
                        ->orderBy('scheduled_date')
                        ->limit(10)
                        ->get(),
                ],

                // Charts Data
                'weekly_trends' => [
                    'tickets' => Ticket::selectRaw('DATE(created_at) as date, count(*) as count')
                        ->where('created_at', '>=', Carbon::now()->subDays(7))
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get(),
                    'maintenances_completed' => AMCMaintenance::selectRaw('DATE(completed_date) as date, count(*) as count')
                        ->where('completed_date', '>=', Carbon::now()->subDays(7))
                        ->whereNotNull('completed_date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Operator Dashboard
     */
    public function operatorDashboard(): JsonResponse
    {
        try {
            $data = [
                // Tickets Overview
                'tickets' => [
                    'total' => Ticket::count(),
                    'pending' => Ticket::where('status', 'pending')->count(),
                    'in_progress' => Ticket::where('status', 'in_progress')->count(),
                    'completed' => Ticket::where('status', 'completed')->count(),
                    'unassigned' => Ticket::whereNull('assigned_to')->count(),
                    'high_priority' => Ticket::where('priority', 'high')
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->count(),
                    'today' => Ticket::whereDate('created_at', Carbon::today())->count(),
                    'this_week' => Ticket::whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                ],

                // AMC Maintenances
                'amc_maintenances' => [
                    'total' => AMCMaintenance::count(),
                    'pending' => AMCMaintenance::where('status', 'pending')->count(),
                    'in_progress' => AMCMaintenance::where('status', 'in_progress')->count(),
                    'scheduled_today' => AMCMaintenance::whereDate('scheduled_date', Carbon::today())->count(),
                    'scheduled_this_week' => AMCMaintenance::whereBetween('scheduled_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->count(),
                    'overdue' => AMCMaintenance::where('status', 'pending')
                        ->where('scheduled_date', '<', Carbon::today())
                        ->count(),
                    'unassigned' => AMCMaintenance::whereNull('assigned_to')
                        ->where('status', 'pending')
                        ->count(),
                ],

                // Technician Availability
                'technicians' => [
                    'total' => User::where('role_as', User::ROLE_TECHNICIAN)
                        ->where('is_active', true)
                        ->count(),
                    'on_duty' => Track::whereNull('ended_at')
                        ->distinct('technician_id')
                        ->count('technician_id'),
                    'available' => User::where('role_as', User::ROLE_TECHNICIAN)
                        ->where('is_active', true)
                        ->whereNotIn('id', function($query) {
                            $query->select('technician_id')
                                ->from('tracks')
                                ->whereNull('ended_at');
                        })
                        ->count(),
                ],

                // Customers
                'customers' => [
                    'total' => User::where('role_as', User::ROLE_CUSTOMER)->count(),
                    'active' => User::where('role_as', User::ROLE_CUSTOMER)
                        ->where('is_active', true)
                        ->count(),
                ],

                // Inventory Alerts
                'inventory' => [
                    'low_stock' => ShopInventory::where('quantity', '<', 10)->count(),
                    'out_of_stock' => ShopInventory::where('quantity', 0)->count(),
                    'low_stock_items' => ShopInventory::where('quantity', '<', 10)
                        ->orderBy('quantity')
                        ->limit(5)
                        ->get(['id', 'product_name', 'quantity', 'category']),
                ],

                // Recent Activities
                'recent_tickets' => Ticket::with('assignedTechnician:id,name')
                    ->latest()
                    ->limit(10)
                    ->get(['id', 'title', 'status', 'priority', 'assigned_to', 'created_at']),

                'upcoming_maintenances' => AMCMaintenance::with('assignedTechnician:id,name', 'amcContract:id')
                    ->where('scheduled_date', '>=', Carbon::today())
                    ->orderBy('scheduled_date')
                    ->limit(10)
                    ->get(),

                // Priority Alerts
                'alerts' => [
                    'unassigned_tickets' => Ticket::whereNull('assigned_to')
                        ->where('status', 'pending')
                        ->count(),
                    'overdue_maintenances' => AMCMaintenance::where('status', 'pending')
                        ->where('scheduled_date', '<', Carbon::today())
                        ->count(),
                    'high_priority_tickets' => Ticket::where('priority', 'high')
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->count(),
                ],

                // Daily Stats
                'daily_stats' => [
                    'tickets_created' => Ticket::whereDate('created_at', Carbon::today())->count(),
                    'tickets_completed' => Ticket::whereDate('completed_at', Carbon::today())->count(),
                    'maintenances_scheduled' => AMCMaintenance::whereDate('scheduled_date', Carbon::today())->count(),
                    'maintenances_completed' => AMCMaintenance::whereDate('completed_date', Carbon::today())->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Technician Dashboard
     */
    public function technicianDashboard(Request $request): JsonResponse
    {
        try {
            $technicianId = Auth::id();

            $data = [
                // My Tasks
                'my_tickets' => [
                    'total' => Ticket::where('assigned_to', $technicianId)->count(),
                    'pending' => Ticket::where('assigned_to', $technicianId)
                        ->where('status', 'pending')
                        ->count(),
                    'in_progress' => Ticket::where('assigned_to', $technicianId)
                        ->where('status', 'in_progress')
                        ->count(),
                    'completed_today' => Ticket::where('assigned_to', $technicianId)
                        ->whereDate('completed_at', Carbon::today())
                        ->count(),
                    'completed_this_week' => Ticket::where('assigned_to', $technicianId)
                        ->whereBetween('completed_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ])
                        ->count(),
                    'completed_this_month' => Ticket::where('assigned_to', $technicianId)
                        ->whereMonth('completed_at', Carbon::now()->month)
                        ->whereYear('completed_at', Carbon::now()->year)
                        ->count(),
                ],

                'my_maintenances' => [
                    'total' => AMCMaintenance::where('assigned_to', $technicianId)->count(),
                    'pending' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->where('status', 'pending')
                        ->count(),
                    'in_progress' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->where('status', 'in_progress')
                        ->count(),
                    'scheduled_today' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->whereDate('scheduled_date', Carbon::today())
                        ->count(),
                    'scheduled_this_week' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->whereBetween('scheduled_date', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ])
                        ->count(),
                    'overdue' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->where('status', 'pending')
                        ->where('scheduled_date', '<', Carbon::today())
                        ->count(),
                ],

                // My Tracking
                'my_tracking' => [
                    'active_track' => Track::where('technician_id', $technicianId)
                        ->whereNull('ended_at')
                        ->with(['ticket:id,title', 'amcMaintenance:id,note'])
                        ->first(),
                    'tracks_today' => Track::where('technician_id', $technicianId)
                        ->whereDate('started_at', Carbon::today())
                        ->count(),
                    'total_distance_today' => $this->calculateTechnicianDistanceToday($technicianId),
                    'tracks_this_week' => Track::where('technician_id', $technicianId)
                        ->whereBetween('started_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ])
                        ->count(),
                ],

                // My Tasks Lists
                'today_tickets' => Ticket::where('assigned_to', $technicianId)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->with('customer:id,name,phone,address')
                    ->latest()
                    ->limit(10)
                    ->get(['id', 'customer_id', 'title', 'description', 'status', 'priority', 'district', 'city', 'created_at']),

                'today_maintenances' => AMCMaintenance::where('assigned_to', $technicianId)
                    ->whereDate('scheduled_date', Carbon::today())
                    ->with(['amcContract.customer:id,name,phone,address', 'amcContract.branch:id,name,address_line1,city'])
                    ->get(),

                'upcoming_maintenances' => AMCMaintenance::where('assigned_to', $technicianId)
                    ->where('scheduled_date', '>', Carbon::today())
                    ->where('scheduled_date', '<=', Carbon::today()->addDays(7))
                    ->with(['amcContract.customer:id,name,phone', 'amcContract.branch:id,name,city'])
                    ->orderBy('scheduled_date')
                    ->get(),

                // Performance Stats
                'performance' => [
                    'completion_rate' => $this->calculateCompletionRate($technicianId),
                    'average_completion_time' => $this->calculateAverageCompletionTime($technicianId),
                    'total_completed_tickets' => Ticket::where('assigned_to', $technicianId)
                        ->where('status', 'completed')
                        ->count(),
                    'total_completed_maintenances' => AMCMaintenance::where('assigned_to', $technicianId)
                        ->where('status', 'completed')
                        ->count(),
                ],

                // Weekly Activity Chart
                'weekly_activity' => [
                    'tickets' => Ticket::selectRaw('DATE(completed_at) as date, count(*) as count')
                        ->where('assigned_to', $technicianId)
                        ->where('completed_at', '>=', Carbon::now()->subDays(7))
                        ->whereNotNull('completed_at')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get(),
                    'maintenances' => AMCMaintenance::selectRaw('DATE(completed_date) as date, count(*) as count')
                        ->where('assigned_to', $technicianId)
                        ->where('completed_date', '>=', Carbon::now()->subDays(7))
                        ->whereNotNull('completed_date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper Methods
    private function calculateTechnicianDistanceToday($technicianId)
    {
        $tracks = Track::where('technician_id', $technicianId)
            ->whereDate('started_at', Carbon::today())
            ->with('points')
            ->get();

        $totalDistance = 0;
        foreach ($tracks as $track) {
            $totalDistance += $this->calculateTrackDistance($track->points);
        }

        return round($totalDistance, 2);
    }

    private function calculateTrackDistance($points)
    {
        if ($points->count() < 2) {
            return 0;
        }

        $totalDistance = 0;
        $previousPoint = null;

        foreach ($points as $point) {
            if ($previousPoint) {
                $totalDistance += $this->haversineDistance(
                    $previousPoint->lat,
                    $previousPoint->lng,
                    $point->lat,
                    $point->lng
                );
            }
            $previousPoint = $point;
        }

        return $totalDistance;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function calculateCompletionRate($technicianId)
    {
        $totalAssigned = Ticket::where('assigned_to', $technicianId)->count();
        if ($totalAssigned === 0) {
            return 0;
        }

        $completed = Ticket::where('assigned_to', $technicianId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $totalAssigned) * 100, 2);
    }

    private function calculateAverageCompletionTime($technicianId)
    {
        $tickets = Ticket::where('assigned_to', $technicianId)
            ->where('status', 'completed')
            ->whereNotNull('accepted_at')
            ->whereNotNull('completed_at')
            ->get(['accepted_at', 'completed_at']);

        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($tickets as $ticket) {
            $totalMinutes += Carbon::parse($ticket->accepted_at)
                ->diffInMinutes(Carbon::parse($ticket->completed_at));
        }

        return round($totalMinutes / $tickets->count(), 2);
    }
}
