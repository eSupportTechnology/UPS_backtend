<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GetTicketById
{
    public function __invoke(string $ticketId): array
    {
        try {
            $ticket = Ticket::where('id', $ticketId)->first();

            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket not found',
                ];
            }

            // Get customer info
            $customer = User::find($ticket->customer_id);
            $technician = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;

            // Get branch info if branch_id exists
            $branch = null;
            if ($ticket->branch_id) {
                $branch = DB::table('company_branches')->where('id', $ticket->branch_id)->first();
            }

            // Build response with all necessary data
            $ticketData = [
                'id' => $ticket->id,
                'job_type' => $ticket->job_type,
                'job_number' => $ticket->job_number,
                'customer_id' => $ticket->customer_id,
                'branch_id' => $ticket->branch_id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'address' => $ticket->address,
                'photo_paths' => $ticket->photo_paths ? json_decode($ticket->photo_paths, true) : [],
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'assigned_to' => $ticket->assigned_to,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'accepted_at' => $ticket->accepted_at,
                'completed_at' => $ticket->completed_at,
                'district' => $ticket->district,
                'city' => $ticket->city,
                'gramsewa_division' => $ticket->gramsewa_division,
                'customer_name' => $customer ? $customer->name : null,
                'customer_email' => $customer ? $customer->email : null,
                'customer_phone' => $customer ? $customer->phone : null,
                'customer_type' => $customer ? $customer->customer_type : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'is_primary' => $branch ? $branch->is_primary : null,
                'technician_name' => $technician ? $technician->name : null,
                'technician_email' => $technician ? $technician->email : null,
                'technician_phone' => $technician ? $technician->phone : null,
            ];

            return [
                'success' => true,
                'message' => 'Ticket retrieved successfully',
                'data' => $ticketData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve ticket: ' . $e->getMessage(),
            ];
        }
    }
}
