<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEvaluation extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'type',
        'score',
        'comment',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'date',
        'score'        => 'integer',
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
