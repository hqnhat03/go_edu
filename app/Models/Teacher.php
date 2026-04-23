<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'nationality',
        'expertise',
        'experience',
        'target_student',
        'bio'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lớp giáo viên đang phụ trách (many-to-many).
     */
    public function teachingClasses()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_teachers', 'teacher_id', 'class_id');
    }

    /**
     * Buổi học do giáo viên tạo.
     */
    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'teacher_id');
    }

    /**
     * Bài kiểm tra do giáo viên tạo.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    /**
     * Đánh giá học sinh do giáo viên thực hiện.
     */
    public function evaluations()
    {
        return $this->hasMany(StudentEvaluation::class, 'teacher_id');
    }

    /**
     * Thông báo lớp do giáo viên đăng.
     */
    public function announcements()
    {
        return $this->hasMany(ClassAnnouncement::class, 'teacher_id');
    }
}
