<?php

namespace App\Http\Controllers;

use App\Action\Ticket\AcceptTicket;
use App\Action\Ticket\AssignTicket;
use App\Action\Ticket\CompleteTicket;
use App\Action\Ticket\CreateTicket;
use App\Action\Ticket\GetAllTickets;
use App\Action\Ticket\GetTicketsByAssignedTo;
use App\Action\Ticket\GetTicketsByCustomer;
use App\Action\Ticket\ExportTicketsExcel;
use App\Action\Ticket\ExportTicketsPdf;
use App\Action\Ticket\GenerateTicketReport;
use App\Http\Requests\Ticket\AcceptTicketRequest;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\CompleteTicketRequest;
use App\Http\Requests\Ticket\GetAllTicketsRequest;
use App\Http\Requests\Ticket\GetTicketsByAssignedToRequest;
use App\Http\Requests\Ticket\GetTicketsByCustomerRequest;
use App\Http\Requests\Ticket\TicketRequest;
use App\Http\Requests\Ticket\TicketReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function exportExcel(TicketReportRequest $request, ExportTicketsExcel $action): BinaryFileResponse
    {
        return $action($request->validated());
    }

    public function exportPdf(TicketReportRequest $request, ExportTicketsPdf $action): Response
    {
        return $action($request->validated());
    }

    public function generateReport(TicketReportRequest $request, GenerateTicketReport $action): JsonResponse
    {
        return response()->json($action($request->validated()));
    }
}
