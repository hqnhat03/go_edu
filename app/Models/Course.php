<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

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

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function classRoomsCount()
    {
        return $this->hasMany(ClassRoom::class)->count();
    }

    public function courseMarterials()
    {
        return DB::table('course_marterials')->where('course_id', $this->id)->get();
    }


}
