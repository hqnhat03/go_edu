<?php

namespace App\Models;

class Teacher extends User
{
    /**
     * Các trường có thể được gán hàng loạt (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expertise',             // Chuyên môn
        'experience',            // Kinh nghiệm
        'target_student_type',   // Người học (tất cả, học sinh, nhân viên)
    ];

    /**
     * Ép kiểu dữ liệu (Casting).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Mối quan hệ: Giáo viên này đang giảng dạy các lớp nào.
     */
    public function teachingClasses()
    {
        // Tuỳ vào thiết kế Database của bạn:
        // Nếu 1 giáo viên phụ trách nhiều lớp độc lập:
        // return $this->hasMany(Classroom::class, 'teacher_id');

        // Nếu 1 lớp có nhiều giáo viên và 1 giáo viên dạy nhiều lớp (many-to-many):
        // return $this->belongsToMany(Classroom::class, 'class_teacher', 'teacher_id', 'class_id');
    }
}
