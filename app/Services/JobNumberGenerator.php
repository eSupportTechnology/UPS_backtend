<?php

namespace App\Services;

use App\Models\JobNumberSequence;
use Illuminate\Support\Facades\DB;

class JobNumberGenerator
{
    /**
     * Generate next job number for inside jobs
     * Format: INJ-2025-0001
     *
     * @return string
     */
    public static function generateInsideJobNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = 'INJ';

            $sequence = JobNumberSequence::lockForUpdate()
                ->firstOrCreate(
                    ['year' => $year, 'prefix' => $prefix],
                    ['sequence' => 0]
                );

            $sequence->increment('sequence');
            $sequence->refresh();

            return sprintf(
                '%s-%d-%04d',
                $prefix,
                $year,
                $sequence->sequence
            );
        });
    }
}
