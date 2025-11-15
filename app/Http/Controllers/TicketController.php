<?php

namespace App\Http\Controllers;

use App\Action\Ticket\AcceptTicket;
use App\Action\Ticket\AssignTicket;
use App\Action\Ticket\CompleteTicket;
use App\Action\Ticket\CreateTicket;
use App\Action\Ticket\GetAllTickets;
use App\Action\Ticket\GetTicketsByAssignedTo;
use App\Action\Ticket\GetTicketsByCustomer;
use App\Http\Requests\Ticket\AcceptTicketRequest;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\CompleteTicketRequest;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketsByAssignedToRequest;
use App\Http\Requests\Ticket\GetTicketsByCustomerRequest;
use App\Http\Requests\Ticket\TicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function createTicket(TicketRequest $request, CreateTicket $createTicket): JsonResponse
    {
        return response()->json($createTicket($request->validated()));
    }

    public function getAllTickets(GetAllTicketsRequest $request, GetAllTickets $getAllTickets): JsonResponse
    {
        $result = $getAllTickets($request->validated());
        return response()->json($result);
    }

    public function getTicketsByCustomer(GetTicketsByCustomerRequest $request, GetTicketsByCustomer $action): JsonResponse
    {
        $filters = $request->validated();
        $result = $action($filters);
        return response()->json($result);
    }

    public function assignTicket(AssignTicketRequest $request, AssignTicket $assignTicket): JsonResponse
    {
        return response()->json($assignTicket($request->validated()));
    }

    public function acceptTicket(AcceptTicketRequest $request, AcceptTicket $acceptTicket): JsonResponse
    {
        return response()->json($acceptTicket($request->validated()));
    }

    public function completeTicket(CompleteTicketRequest $request, CompleteTicket $completeTicket): JsonResponse
    {
        return response()->json($completeTicket($request->validated()));
    }

    public function getTicketsByAssignedTo(
        string $assigned_to,
        GetTicketsByAssignedToRequest $request,
        GetTicketsByAssignedTo $getTicketsByAssignedTo
    ): JsonResponse {
        $result = $getTicketsByAssignedTo($assigned_to, $request->validated());
        return response()->json($result);
    }

    public function indexForOperator(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status'    => 'nullable|in:pending,accepted,completed,rejected,on_site',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'product_id'=> 'nullable|string',
        ]);

        $query = Ticket::query()->with(['customer:id,name', 'technician:id,name']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($tickets);
    }

    public function show(string $id): JsonResponse
    {
        $ticket = Ticket::with(['customer', 'technician', 'attachments', 'product'])->findOrFail($id);
        return response()->json($ticket);
    }

    public function updateStatus(string $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,accepted,completed,rejected,on_site',
            'remarks' => 'nullable|string',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);

        // here you could send email/SMS or create notification

        return response()->json($ticket);
    }

    public function assignTicketToTechnician(string $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update([
            'assigned_to' => $data['technician_id'],
            'status'      => 'pending',
        ]);

        // could also push notification to technician

        return response()->json([
            'message' => 'Technician assigned',
            'ticket'  => $ticket,
        ]);
    }

    public function storeCustomerFeedback(string $id, Request $request): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate([
            'rating'  => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'customer_rating'  => $data['rating'] ?? $ticket->customer_rating,
            'customer_comment' => $data['comment'] ?? $ticket->customer_comment,
        ]);

        return response()->json([
            'message' => 'Feedback stored',
            'ticket'  => $ticket,
        ]);
    }

}
