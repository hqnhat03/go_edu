<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'status',
    ];

    protected $hidden = [
        'slug',
        'created_at',
        'updated_at',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function countCourses()
    {
        return $this->courses()->count();
    }
}
