<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'name',
        'duration_minutes',
        'open_at',
        'close_at',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'open_at'          => 'datetime',
        'close_at'         => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Lớp học chứa bài kiểm tra.
     */
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Giáo viên tạo bài kiểm tra.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Danh sách câu hỏi của bài kiểm tra.
     */
    public function questions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id')->orderBy('order_number', 'asc');
    }

    /**
     * Kết quả bài làm của học sinh.
     */
    public function results()
    {
        return $this->hasMany(ExamResult::class, 'exam_id');
    }
}
