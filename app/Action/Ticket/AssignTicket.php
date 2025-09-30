<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Models\User;
use App\Response\CommonResponse;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignTicket
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            $assignedUser = User::findOrFail($data['assigned_to']);

            $ticket->assigned_to = $data['assigned_to'];
            if (!empty($data['priority'])) {
                $ticket->priority = $data['priority'];
            }
            $ticket->status = 'assigned';
            $ticket->save();

            DB::commit();

            if (!empty($assignedUser->phone)) {
                $this->sendSmsNotification($assignedUser, $ticket);
            } else {
                Log::warning('Cannot send SMS: User has no phone number', [
                    'user_id' => $assignedUser->id,
                    'ticket_id' => $ticket->id,
                ]);
            }

            return CommonResponse::sendSuccessResponse('Ticket assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to assign ticket: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to assign ticket');
        }
    }

    protected function sendSmsNotification(User $user, Ticket $ticket): void
    {
        try {
            $priority = ucfirst($ticket->priority ?? 'normal');

            $message = sprintf(
                "Hello %s, Ticket #%s (Priority: %s) has been assigned to you. Please check your dashboard.",
                $user->name,
                $ticket->id,
                $priority
            );

            $result = $this->smsService->send($user->phone, $message);

            if ($result['success']) {
                Log::info('SMS notification sent for ticket assignment', [
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                ]);
            } else {
                Log::warning('SMS notification failed for ticket assignment', [
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'error' => $result['message'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS notification', [
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
