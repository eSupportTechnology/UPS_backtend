<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Response\CommonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTicket
{
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            if (isset($data['photos']) && is_array($data['photos'])) {
                $paths = [];
                foreach ($data['photos'] as $photo) {
                    $paths[] = $photo->store('tickets', 'public');
                }
                $data['photo_paths'] = $paths;

                unset($data['photos']);
            }

            Ticket::create($data);

            DB::commit();

            return CommonResponse::sendSuccessResponse('Ticket created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create ticket: ' . $e->getMessage(), ['data' => $data]);

            return CommonResponse::sendBadResponseWithMessage('Failed to create ticket');
        }
    }
}
