<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'causer_id',
        'causer_type',
        'action',
        'subject_id',
        'subject_type',
        'visibility',
        'group_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the actor who performed the activity.
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Get the subject that was acted upon.
     */
    public function subject()
    {
        return $this->morphTo();
    }
}
