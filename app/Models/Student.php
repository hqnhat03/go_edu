<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends User
{

    use HasFactory;
    protected $fillable = [
        "student_type",
        "school",
        "grade",
        "work",
        "position",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians', 'student_id', 'guardian_id');
    }
}
