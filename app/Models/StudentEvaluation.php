<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEvaluation extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'string',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Học sinh được đánh giá.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Lớp học liên quan.
     */
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Giáo viên thực hiện đánh giá.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
