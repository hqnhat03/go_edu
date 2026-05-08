<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\StudentAttendanceStat;
use Illuminate\Support\Facades\DB;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        $this->updateStats($attendance);
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        $this->updateStats($attendance);
    }

    /**
     * Handle the Attendance "deleted" event.
     */
    public function deleted(Attendance $attendance): void
    {
        $this->updateStats($attendance);
    }

    /**
     * Update the aggregated stats for the student in the specific class.
     */
    private function updateStats(Attendance $attendance): void
    {
        // Lấy class_id từ session
        $classId = DB::table('class_sessions')
            ->where('id', $attendance->class_session_id)
            ->value('class_id');

        if (!$classId) return;

        $studentId = $attendance->student_id;

        // Tính toán lại tất cả các chỉ số của học sinh này trong lớp này
        $stats = DB::table('attendances')
            ->join('class_sessions', 'attendances.class_session_id', '=', 'class_sessions.id')
            ->where('attendances.student_id', $studentId)
            ->where('class_sessions.class_id', $classId)
            ->select(
                DB::raw('count(*) as total'),
                DB::raw("sum(case when attendances.status = 'present' then 1 else 0 end) as present"),
                DB::raw("sum(case when attendances.status = 'late' then 1 else 0 end) as late"),
                DB::raw("sum(case when attendances.status = 'absent' then 1 else 0 end) as absent")
            )
            ->first();

        // Cập nhật hoặc tạo mới vào bảng Summary
        StudentAttendanceStat::updateOrCreate(
            ['student_id' => $studentId, 'class_id' => $classId],
            [
                'total_sessions' => $stats->total ?? 0,
                'present_count'  => $stats->present ?? 0,
                'late_count'     => $stats->late ?? 0,
                'absent_count'   => $stats->absent ?? 0,
            ]
        );
    }
}
