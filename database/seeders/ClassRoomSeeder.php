<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class_rooms = [
            [
                'class_code' => 'C001',
                'start_day' => '2022-01-01',
                'end_day' => '2022-12-31',
                'max_student' => 10,
                'meeting_url' => 'https://meet.google.com/abc-def-ghi',
                'status' => 'published',
                'course_id' => 1,
                'teachers' => [1, 2]
            ]
        ];

        DB::transaction(function () use ($class_rooms) {
            foreach ($class_rooms as $class_room) {

                // 1. insert class_room và lấy model
                $class = ClassRoom::create([
                    'class_code' => $class_room['class_code'],
                    'start_day' => $class_room['start_day'],
                    'end_day' => $class_room['end_day'],
                    'max_student' => $class_room['max_student'],
                    'meeting_url' => $class_room['meeting_url'],
                    'status' => $class_room['status'],
                    'course_id' => $class_room['course_id'],
                ]);

                // 2. map teachers → pivot table
                $teachers = collect($class_room['teachers'])->map(function ($teacherId) use ($class) {
                    return [
                        'id' => Str::uuid(),
                        'class_id' => $class->id,
                        'teacher_id' => $teacherId,
                    ];
                })->toArray();

                DB::table('class_teachers')->insert($teachers);
            }
        });

    }
}
