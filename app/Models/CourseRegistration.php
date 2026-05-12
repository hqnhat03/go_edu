<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class CourseRegistration extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'course_id',
        'name',
        'email',
        'phone',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the course that owns the registration.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
