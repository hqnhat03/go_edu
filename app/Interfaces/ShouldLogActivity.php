<?php

namespace App\Interfaces;

/**
 * Interface ShouldLogActivity
 * 
 * Mark an event as loggable by the ActivityLogger listener.
 */
interface ShouldLogActivity
{
    /**
     * Get the data to be logged.
     * 
     * Expected keys: action, subject, description, properties, visibility, group_id, causer
     *
     * @return array
     */
    public function getLogData(): array;
}
