<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\StudentAttendanceStat;

class SyncAttendanceStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync aggregated attendance stats from raw attendance data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync...');

        // Xóa sạch bảng cũ để tính lại từ đầu cho chính xác
        StudentAttendanceStat::truncate();

        // Lấy tất cả các cặp student_id và class_id có trong bảng điểm danh
        $combinations = DB::table('attendances')
            ->join('class_sessions', 'attendances.class_session_id', '=', 'class_sessions.id')
            ->select('attendances.student_id', 'class_sessions.class_id')
            ->distinct()
            ->get();

        $bar = $this->output->createProgressBar(count($combinations));
        $bar->start();

        foreach ($combinations as $combo) {
            $stats = DB::table('attendances')
                ->join('class_sessions', 'attendances.class_session_id', '=', 'class_sessions.id')
                ->where('attendances.student_id', $combo->student_id)
                ->where('class_sessions.class_id', $combo->class_id)
                ->select(
                    DB::raw('count(*) as total'),
                    DB::raw("sum(case when attendances.status = 'present' then 1 else 0 end) as present"),
                    DB::raw("sum(case when attendances.status = 'late' then 1 else 0 end) as late"),
                    DB::raw("sum(case when attendances.status = 'absent' then 1 else 0 end) as absent")
                )
                ->first();

            StudentAttendanceStat::create([
                'student_id'     => $combo->student_id,
                'class_id'       => $combo->class_id,
                'total_sessions' => $stats->total ?? 0,
                'present_count'  => $stats->present ?? 0,
                'late_count'     => $stats->late ?? 0,
                'absent_count'   => $stats->absent ?? 0,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSync completed successfully!");
    }
}
