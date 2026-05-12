<?php

namespace App\Listeners;

use App\Interfaces\ShouldLogActivity;
use App\Services\ActivityLogService;

class ActivityLogger
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof ShouldLogActivity) {
            $data = $event->getLogData();

            $this->activityLogService->log(
                action: $data['action'],
                subject: $data['subject'] ?? null,
                description: $data['description'] ?? null,
                properties: $data['properties'] ?? [],
                visibility: $data['visibility'] ?? 'admin',
                groupId: $data['group_id'] ?? null,
                causer: $data['causer'] ?? null
            );
        }
    }
}
