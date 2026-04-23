<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExamQuestion extends Model
{
    use HasUuids;
    protected $fillable = [
        'id',
        'exam_id',
        'question',
        'type',
        'options',
        'correct_answer',
        'score',
        'order_number',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'score' => 'float',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Bài kiểm tra chứa câu hỏi này.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
