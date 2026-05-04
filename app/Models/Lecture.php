<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $fillable = [
        'name',
        'duration_time',
        'lecture_number',
        'document_url',
        'video_url',
        'description',
        'status',
        'teacher_id',
        'class_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Lớp học chứa buổi học này.
     */
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Giáo viên tạo buổi học.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
