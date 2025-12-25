<?php

namespace App\Action\Ticket;

use App\Models\Ticket;
use App\Models\QuoteLineItem;
use App\Response\CommonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateQuote
{
    /**
     * Create quote for inside job repair
     */
    public function __invoke(array $data): array
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::findOrFail($data['ticket_id']);

            if ($ticket->job_type !== 'inside') {
                throw new \Exception('Can only create quotes for inside jobs');
            }

            // Delete existing quote line items if re-quoting
            QuoteLineItem::where('ticket_id', $ticket->id)->delete();

            $totalAmount = 0;
            $quoteData = [];

            foreach ($data['line_items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $lineTotal;

                $lineItem = QuoteLineItem::create([
                    'ticket_id' => $ticket->id,
                    'item_type' => $item['item_type'],
                    'inventory_id' => $item['inventory_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $lineTotal,
                ]);

                $quoteData[] = [
                    'id' => $lineItem->id,
                    'type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ];
            }

            $ticket->update([
                'quote_data' => $quoteData,
                'quote_total' => $totalAmount,
                'quoted_at' => Carbon::now(),
                'quoted_by' => $data['quoted_by'],
                'approval_status' => 'pending',
                'status' => 'quoted',
            ]);

            DB::commit();

            return CommonResponse::sendSuccessResponse(
                'Quote created successfully',
                ['ticket' => $ticket->fresh(['quoteLineItems'])]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create quote: ' . $e->getMessage(), ['data' => $data]);
            return CommonResponse::sendBadResponseWithMessage('Failed to create quote');
        }
    }
}
