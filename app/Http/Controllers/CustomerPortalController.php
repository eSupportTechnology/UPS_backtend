<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerPortalController extends Controller
{
    public function serviceHistory(Request $request): JsonResponse
    {
        $customer = $request->user();

        $tickets = Ticket::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->with(['technician:id,name', 'product'])
            ->paginate(20);

        return response()->json($tickets);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:500',
        ]);

        $customer->update($data);

        return response()->json($customer);
    }

    public function uploadTicketAttachment(int $ticket_id, Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $ticket = Ticket::where('id', $ticket_id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $path = $request->file('file')->store("tickets/{$ticket_id}", 'public');

        $attachment = $ticket->attachments()->create([
            'path'        => $path,
            'uploaded_by' => $request->user()->id,
            'role'        => 'customer',
        ]);

        return response()->json($attachment, 201);
    }
}
