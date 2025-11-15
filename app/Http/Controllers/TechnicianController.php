<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnicianController extends Controller
{
    public function arrive(int $id, Request $request): JsonResponse
    {
        $ticket = Ticket::where('id', $id)
            ->where('assigned_to', $request->user()->id)
            ->firstOrFail();

        $ticket->update([
            'arrived_at' => now(),
            'status'     => 'on_site',
        ]);

        return response()->json([
            'message' => 'Arrival recorded',
            'ticket'  => $ticket,
        ]);
    }

    public function uploadAttachment(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $ticket = Ticket::where('id', $id)
            ->where('assigned_to', $request->user()->id)
            ->firstOrFail();

        $path = $request->file('file')->store("tickets/{$id}", 'public');

        $attachment = $ticket->attachments()->create([
            'path'        => $path,
            'uploaded_by' => $request->user()->id,
            'role'        => 'technician',
        ]);

        return response()->json($attachment, 201);
    }

    public function history(Request $request): JsonResponse
    {
        $tickets = Ticket::where('assigned_to', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($tickets);
    }
}
