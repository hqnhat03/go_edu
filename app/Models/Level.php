<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'level',
        'level_normalized',
        'status',
        'created_by',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
