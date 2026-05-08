<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendanceStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'total_sessions',
        'present_count',
        'late_count',
        'absent_count',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
