<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "category",
        "training_level",
        "student_type",
        "status",
        "created_by"
    ];
}
