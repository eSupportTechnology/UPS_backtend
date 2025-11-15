<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // Simple demo trigger for expiry reminders (for SuperAdmin only)
    public function triggerTestExpiry(): JsonResponse
    {
        // Here you would normally query AMC / warranty expiry and create notifications.
        // For now we just create a dummy one for the first admin.

        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            return response()->json(['message' => 'No admin found'], 404);
        }

        Notification::create([
            'user_id' => $admin->id,
            'title'   => 'Test AMC Expiry',
            'body'    => 'This is a test notification for upcoming AMC expiry.',
            'type'    => 'amc_expiry',
            'is_read' => false,
            'created_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Test notification created for admin']);
    }
}
