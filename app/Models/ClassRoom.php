<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_code',
        'start_day',
        'end_day',
        'max_student',
        'meeting_url',
        'status',
        'course_id'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'class_teachers', 'class_id', 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_students', 'class_id', 'student_id');
    }

    public function schedules()
    {
        // return $this->hasMany(::class);
    }
}
