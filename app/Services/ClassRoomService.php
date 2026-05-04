<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassTeacher;
use Carbon\Carbon;
use DB;
use Illuminate\Database\QueryException;
use Str;

class ClassRoomService
{
    public function getList(array $params)
    {
        $query = ClassRoom::query()
            ->has('course');


        if (isset($params['course_id'])) {
            $query->where('course_id', $params['course_id']);
        }

        if (isset($params['start_day'])) {
            $query->where('start_day', '>=', $params['start_day']);
        }

        if (isset($params['end_day'])) {
            $query->where('end_day', '<=', $params['end_day']);
        }

        if (isset($params['class_code'])) {
            $query->where('class_code', 'like', '%' . $params['class_code'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['teacher_name'])) {
            $query->whereHas('teachers.user', function ($q) use ($params) {
                $q->where('name', 'like', '%' . $params['teacher_name'] . '%');
            });
        }

        $data = $query->with(['teachers.user'])
            ->withCount('students')
            ->get();

        return $data->map(function ($class) {
            return [
                'id' => $class->id,
                'class_code' => $class->class_code,
                'start_day' => $class->start_day,
                'end_day' => $class->end_day,
                'status' => $class->status,
                'teachers' => $class->teachers->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->user->name,
                    ];
                }),
                'student_count' => $class->students_count,
            ];
        });
    }

    public function findById(int $id)
    {
        $class = ClassRoom::with(['course', 'teachers', 'schedules'])->findOrFail($id);
        return [
            ...$class->only('id', 'class_code', 'start_day', 'end_day', 'max_student', 'meeting_url', 'status'),
            'teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'avatar' => $teacher->user->avatar,
                ];
            }),
            'class_teaches' => ClassTeacher::where('class_id', $class->id)
                ->get(['id', 'teacher_id'])
                ->toArray(),
            // danh sach hoc sinh
            'students' => $class->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'avatar' => $student->user->avatar,
                ];
            }),
            // lich hoc
            'class_schedules' => $class->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                ];
            }),
        ];
    }
    public function create($data)
    {
        try {
            $class = DB::transaction(function () use ($data) {
                $class = ClassRoom::create([
                    'class_code' => $data['class_code'],
                    'start_day' => $data['start_day'],
                    'end_day' => $data['end_day'],
                    'max_student' => $data['max_student'],
                    'meeting_url' => $data['meeting_url'],
                    'status' => $data['status'],
                    'course_id' => $data['course_id'],
                ]);

                if (!empty($data['class_teachers'])) {
                    $class_teachers = collect($data['class_teachers'])->map(function ($value) use ($class) {
                        return [
                            'id' => $value['id'],
                            'class_id' => $class->id,
                            'teacher_id' => $value['teacher_id'],
                        ];
                    })->toArray();
                    ClassTeacher::insert($class_teachers);
                }

                if (!empty($data['class_schedules'])) {
                    $schedules = collect($data['class_schedules'])->map(function ($value) {
                        return [
                            'id' => $value['id'],
                            'day_of_week' => $value['day_of_week'],
                            'start_time' => $value['start_time'],
                            'end_time' => $value['end_time'],
                        ];
                    })->toArray();
                    $class->schedules()->createMany($schedules);
                }

                $sessions = [];

                $start = Carbon::parse($class->start_day);
                $end = Carbon::parse($class->end_day);

                foreach ($schedules as $schedule) {
                    $current = $start->copy();

                    while ($current->lte($end)) {
                        if ($current->dayOfWeek == $schedule['day_of_week']) {
                            $sessions[] = [
                                'class_id' => $class->id,
                                'date' => $current->toDateString(),
                                'start_time' => $schedule['start_time'],
                                'end_time' => $schedule['end_time'],
                                'status' => 'scheduled',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        $current->addDay();
                    }
                }

                // bulk insert
                ClassSession::insert($sessions);
                return $class;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Mã lớp đã tồn tại');
            }
            throw $e;
        }

        return [
            ...$class->only('id', 'class_code', 'start_day', 'end_day', 'status'),
            'class_teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                ];
            }),
            'student_count' => $class->students->count()
        ];
    }
    public function update($data, $id)
    {

        $class = ClassRoom::findOrFail($id);
        try {
            $class = DB::transaction(function () use ($class, $data) {
                $class->update([
                    'class_code' => $data['class_code'],
                    'start_day' => $data['start_day'],
                    'end_day' => $data['end_day'],
                    'max_student' => $data['max_student'],
                    'meeting_url' => $data['meeting_url'],
                    'status' => $data['status'],
                    'course_id' => $data['course_id'],
                ]);

                $class_teachers = collect($data['class_teachers'])->map(function ($value) use ($class) {
                    return [
                        'id' => $value['id'] ?? null,
                        'class_id' => $class->id,
                        'teacher_id' => $value['teacher_id'],
                    ];
                })->toArray();

                $class_teachers_ids = collect($class_teachers)->pluck('id')->toArray();

                // DELETE những cái không còn
                ClassTeacher::where('class_id', $class->id)
                    ->whereNotIn('id', $class_teachers_ids)
                    ->delete();

                // upsert
                ClassTeacher::upsert(
                    $class_teachers,
                    ['id'],
                    ['teacher_id']
                );

                $class_schedules = collect($data['class_schedules'])->map(function ($value) use ($class) {
                    return [
                        'id' => $value['id'] ?? Str::uuid(),
                        'class_id' => $class->id,
                        'day_of_week' => $value['day_of_week'],
                        'start_time' => $value['start_time'],
                        'end_time' => $value['end_time'],
                    ];
                })->toArray();

                $class_schedules_ids = collect($class_schedules)->pluck('id')->filter()->toArray();

                // DELETE những cái không còn
                ClassSchedule::where('class_id', $class->id)
                    ->whereNotIn('id', $class_schedules_ids)
                    ->delete();

                // upsert
                ClassSchedule::upsert(
                    $class_schedules,
                    ['id'],
                    ['day_of_week', 'start_time', 'end_time']
                );

                return $class;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Mã lớp đã tồn tại');
            }
            throw $e;
        }

        return [
            ...$class->only('id', 'class_code', 'start_day', 'end_day', 'max_student', 'meeting_url', 'status'),
            'teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'avatar' => $teacher->user->avatar,
                ];
            }),
            'class_teaches' => ClassTeacher::where('class_id', $class->id)
                ->get(['id', 'teacher_id'])
                ->toArray(),
            // danh sach hoc sinh
            'students' => $class->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'avatar' => $student->user->avatar,
                ];
            }),
            // lich hoc
            'class_schedules' => $class->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                ];
            }),
        ];
    }
    public function delete($id)
    {
        $class = ClassRoom::findOrFail($id);
        // kiem tra du lieu

        $class->delete();
        return $class->id;
    }

    public function assignStudents(int $id, array $studentIds)
    {
        $class = ClassRoom::findOrFail($id);

        return DB::transaction(function () use ($class, $studentIds) {
            $class->students()->syncWithoutDetaching($studentIds);

            DB::table('course_students')
                ->where('course_id', $class->course_id)
                ->whereIn('student_id', $studentIds)
                ->update(['is_assigned' => true]);

            return $class->loadCount('students');
        });
    }
}