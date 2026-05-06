<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAnnouncement extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected $casts = [
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Lớp học nhận thông báo.
     */
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Giáo viên đăng thông báo.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
