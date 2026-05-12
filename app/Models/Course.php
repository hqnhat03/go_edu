<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Course extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'target_student',
        'price',
        'lesson_count',
        'completion_time',
        'image_url',
        'level_id',
        'subject_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class, 'course_id', 'id');
    }

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_students')->withPivot('is_assigned');
    }
}
