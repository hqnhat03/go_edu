<?php

namespace App\Models;

class Student extends User
{
    protected $fillable = [
        "name",
        "password",
        "gender",
        "date_of_birth",
        "phone",
        "address",
        "avatar",
        "student_type",
        "school",
        "level",
        "work",
        "position",
    ];
}
