<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportInsideJobsPdf
{
    public function __invoke(array $filters = []): Response
    {
        try {
            $jobs = $this->getJobs($filters);

            $data = [
                'jobs' => $jobs,
                'filters' => $filters,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'total_jobs' => $jobs->count(),
                'pending_inspection_count' => $jobs->where('status', 'pending_inspection')->count(),
                'inspected_count' => $jobs->where('status', 'inspected')->count(),
                'quoted_count' => $jobs->where('status', 'quoted')->count(),
                'approved_count' => $jobs->where('status', 'approved_for_repair')->count(),
                'in_repair_count' => $jobs->where('status', 'in_repair')->count(),
                'completed_count' => $jobs->where('status', 'completed')->count(),
                'rejected_count' => $jobs->where('status', 'quote_rejected')->count(),
                'high_priority_count' => $jobs->where('priority', 'high')->count(),
                'urgent_priority_count' => $jobs->where('priority', 'urgent')->count(),
                'total_quote_value' => $jobs->sum('quote_total'),
            ];

            $pdf = Pdf::loadView('reports.inside-jobs', $data)
                ->setPaper('a4', 'landscape')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            $fileName = 'inside_jobs_' . now()->format('Y_m_d_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Failed to export inside jobs to PDF: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function getJobs(array $filters)
    {
        $query = Ticket::insideJobs()
            ->with(['customer', 'assignedTechnician', 'inspector', 'quoter', 'plannedMaterials']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('job_number', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('ups_serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $statuses = explode(',', $filters['status']);
                $query->whereIn('status', $statuses);
            }
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['technician'])) {
            $query->where('assigned_to', $filters['technician']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['today']) && $filters['today'] === 'true') {
            $query->whereDate('created_at', now()->toDateString());
        }
    }
}
