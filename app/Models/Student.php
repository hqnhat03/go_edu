<?php

namespace App\Models;

class Student extends User
{
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
}
