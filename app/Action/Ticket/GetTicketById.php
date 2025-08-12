<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class GetTicketById
{
    public function __invoke(int $id): array
    {
        try {
            $ticket = Ticket::find($id);

            if (!$ticket) {
                return [
                    'status' => 404,
                    'message' => 'Ticket not found',
                ];
            }

            
            $user = Auth::user();
            if ($user && $user->role === 'customer' && $ticket->customer_id != $user->id) {
                return [
                    'status' => 403,
                    'message' => 'Access denied: You can only view your own tickets',
                ];
            }

            return [
                'status' => 200,
                'message' => 'Ticket retrieved successfully',
                'data' => [
                    'ticket' => $ticket,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch ticket by ID: ' . $e->getMessage(), [
                'ticket_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 500,
                'message' => 'Error retrieving ticket',
            ];
        }
    }
}