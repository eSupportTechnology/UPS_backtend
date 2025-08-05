<?php

namespace App\Http\Controllers;

use App\Action\Ticket\CreateTicket;
use App\Action\Ticket\GetAllTickets;
use App\Action\Ticket\GetTicketsByCustomer;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketsByCustomerRequest;
use App\Http\Requests\Ticket\TicketRequest;
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
}
