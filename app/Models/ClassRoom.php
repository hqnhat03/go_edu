<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ClassRoom extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'class_code',
        'start_day',
        'end_day',
        'max_student',
        'meeting_url',
        'status',
        'course_id',
        'is_full'
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
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'class_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    public function refreshIsFullStatus()
    {
        $isFull = $this->students()->count() >= $this->max_student;
        $this->update([
            'is_full' => $isFull ? \DB::raw('true') : \DB::raw('false')
        ]);
    }

    protected function getLogGroupId(): ?int
    {
        return $this->id;
    }
}
