<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'course_id',
        'link_url',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
