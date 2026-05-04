<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    use HasFactory;
    protected $fillable = [
        'student_type',
        'school',
        'grade',
        'work',
        'position',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians', 'student_id', 'guardian_id');
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students')->withPivot('is_assigned');
    }

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_students', 'student_id', 'class_id');
    }
}
