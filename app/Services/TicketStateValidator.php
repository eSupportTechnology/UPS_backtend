<?php

namespace App\Services;

class TicketStateValidator
{
    /**
     * Define allowed state transitions for each job type
     *
     * @var array
     */
    private static array $transitions = [
        'outside' => [
            'open' => ['assigned'],
            'assigned' => ['accepted'],
            'accepted' => ['completed', 'transferred_to_workshop'],
        ],
        'inside' => [
            'pending_inspection' => ['inspected'],
            'inspected' => ['quoted'],
            'quoted' => ['approved_for_repair', 'quote_rejected'],
            'approved_for_repair' => ['in_repair'],
            'in_repair' => ['completed'],
        ],
    ];

    /**
     * Check if transition from current status to new status is allowed for a given job type
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @param string $jobType ('outside' or 'inside')
     * @return bool
     */
    public static function canTransitionTo(string $currentStatus, string $newStatus, string $jobType): bool
    {
        $allowedTransitions = self::$transitions[$jobType][$currentStatus] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Get all allowed transitions for a job type and current status
     *
     * @param string $currentStatus
     * @param string $jobType
     * @return array
     */
    public static function getAllowedTransitions(string $currentStatus, string $jobType): array
    {
        return self::$transitions[$jobType][$currentStatus] ?? [];
    }
}
