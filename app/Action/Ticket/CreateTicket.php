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
            // Set job_type to outside if not already set
            if (!isset($data['job_type'])) {
                $data['job_type'] = 'outside';
            }

            if (isset($data['photos']) && is_array($data['photos'])) {
                $paths = [];
                foreach ($data['photos'] as $photo) {
                    $paths[] = $photo->store('tickets', 'public');
                }
                $data['photo_paths'] = json_encode($paths);

                unset($data['photos']);
            }

            $ticket = Ticket::create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => $ticket,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create ticket: ' . $e->getMessage(), ['data' => $data]);

            return [
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage(),
            ];
        }
    }
}
