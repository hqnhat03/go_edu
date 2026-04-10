<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guardian extends User
{
    use HasFactory;

    protected $fillable = [
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardians', 'guardian_id', 'student_id');
    }
}
