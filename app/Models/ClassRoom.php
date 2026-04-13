<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_code',
        'start_day',
        'end_day',
        'max_student',
        'meeting_url',
        'status',
        'course_id'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
