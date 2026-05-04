<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswerDetail extends Model
{
    protected $fillable = [
        'exam_result_id',
        'question_id',
        'answer_content',
        'score',
        'is_correct',
        'teacher_comment',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score'      => 'float',
    ];

    /**
     * Thuộc về kết quả bài thi.
     */
    public function result()
    {
        return $this->belongsTo(ExamResult::class, 'exam_result_id');
    }

    /**
     * Thuộc về câu hỏi thi.
     */
    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
