<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Attendance extends Model
{
    use LogsActivity;
    protected $fillable = [
        'student_id',
        'class_session_id',
        'status',
        'note',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * Học sinh được điểm danh.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Buổi học thực tế (Class Session).
     */
    public function session()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }
}
