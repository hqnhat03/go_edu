<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'answers',
        'score',
        'status',
        'graded_by',
        'graded_at',
        'submitted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'answers'      => 'array',
        'score'        => 'float',
        'status'       => 'string',
        'graded_at'    => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Chi tiết từng câu trả lời.
     */
    public function details()
    {
        return $this->hasMany(ExamAnswerDetail::class, 'exam_result_id');
    }

    /**
     * Bài kiểm tra.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Học sinh nộp bài.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Giáo viên chấm bài.
     */
    public function gradedBy()
    {
        return $this->belongsTo(Teacher::class, 'graded_by');
    }
}
