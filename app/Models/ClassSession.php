<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'schedule_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Danh sách điểm danh của buổi học này.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }
}
