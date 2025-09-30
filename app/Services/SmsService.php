<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $userId;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $senderId;

    public function __construct()
    {
        $this->userId   = config('services.sms.notify_lk.user_id') ?? '';
        $this->apiKey   = config('services.sms.notify_lk.api_key') ?? '';
        $this->apiUrl   = config('services.sms.notify_lk.api_url') ?? '';
        $this->senderId = config('services.sms.notify_lk.sender_id');
    }

    public function send(string $phoneNumber, string $message): array
    {
        try {
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

            if (!str_starts_with($phoneNumber, '94')) {
                $phoneNumber = '94' . ltrim($phoneNumber, '0');
            }

            $response = Http::withoutVerifying()
                ->withOptions([
                    'verify' => false,
                ])
                ->timeout(30)
                ->asForm()
                ->post($this->apiUrl, [
                    'user_id' => $this->userId,
                    'api_key' => $this->apiKey,
                    'sender_id' => $this->senderId,
                    'to' => $phoneNumber,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $result,
                ];
            }

            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendTicketAssignmentNotification(
        string $phoneNumber,
        string $ticketId,
        string $priority = 'normal'
    ): array {
        $message = "New ticket #{$ticketId} has been assigned to you. Priority: {$priority}. Please check your dashboard for details.";

        return $this->send($phoneNumber, $message);
    }
}
