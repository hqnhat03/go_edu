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
            $query->whereRaw('unaccent(class_code) ilike unaccent(?)', ["%{$params['class_code']}%"]);
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['teacher_name'])) {
            $query->whereHas('teachers.user', function ($q) use ($params) {
                $q->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$params['teacher_name']}%"]);
            });
        }

        $data = $query->with(['teachers.user', 'course'])
            ->withCount('students')
            ->get();

        return $data->map(function ($class) {
            return [
                'id' => $class->id,
                'class_code' => $class->class_code,
                'course_id' => $class->course_id,
                'course_name' => $class->course->name,
                'start_day' => $class->start_day,
                'end_day' => $class->end_day,
                'status' => $class->status,
                'is_full' => $class->is_full,
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
            ...$class->only('id', 'class_code', 'course_id', 'start_day', 'end_day', 'max_student', 'meeting_url', 'status', 'is_full'),
            'course_name' => $class->course->name,
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
                    'is_full' => DB::raw('false'),
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

                $this->generateSessions($class, $schedules);
                return $class;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Mã lớp đã tồn tại');
            }
            throw $e;
        }

        return [
            ...$class->only('id', 'class_code', 'course_id', 'start_day', 'end_day', 'status'),
            'course_name' => $class->course->name,
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

        $class = ClassRoom::with('schedules')->findOrFail($id);

        // Kiểm tra xem lịch học hoặc ngày bắt đầu/kết thúc có thay đổi không
        $oldSchedules = $class->schedules->map(function ($s) {
            return [
                'day_of_week' => (int)$s->day_of_week,
                'start_time' => Carbon::parse($s->start_time)->format('H:i:s'),
                'end_time' => Carbon::parse($s->end_time)->format('H:i:s'),
            ];
        })->sortBy(['day_of_week', 'start_time'])->values()->toArray();

        $newSchedules = collect($data['class_schedules'])->map(function ($s) {
            return [
                'day_of_week' => (int)$s['day_of_week'],
                'start_time' => Carbon::parse($s['start_time'])->format('H:i:s'),
                'end_time' => Carbon::parse($s['end_time'])->format('H:i:s'),
            ];
        })->sortBy(['day_of_week', 'start_time'])->values()->toArray();

        $isScheduleChanged = ($class->start_day !== $data['start_day']) ||
            ($class->end_day !== $data['end_day']) ||
            ($oldSchedules !== $newSchedules);

        if ($class->students()->exists()) {
            throw new UserException('Không thể cập nhật lớp học đã có học sinh');
        }

        try {
            $class = DB::transaction(function () use ($class, $data, $isScheduleChanged) {
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

                // Chỉ xóa sessions cũ và tạo lại nếu có thay đổi lịch học
                if ($isScheduleChanged) {
                    ClassSession::where('class_id', $class->id)->delete();
                    $this->generateSessions($class, $class_schedules);
                }

                $class->refreshIsFullStatus();
                return $class;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Mã lớp đã tồn tại');
            }
            throw $e;
        }

        return [
            ...$class->only('id', 'class_code', 'course_id', 'start_day', 'end_day', 'max_student', 'meeting_url', 'status', 'is_full'),
            'course_name' => $class->course->name,
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
        $class = ClassRoom::withCount('students')->findOrFail($id);

        // Kiểm tra xem có vượt quá giới hạn không
        $currentCount = $class->students_count;
        $newStudentsCount = count($studentIds);

        if ($currentCount + $newStudentsCount > $class->max_student) {
            throw new UserException("Lớp học đã đầy hoặc số lượng thêm vào vượt quá giới hạn cho phép");
        }

        return DB::transaction(function () use ($class, $studentIds) {
            $class->students()->syncWithoutDetaching($studentIds);

            DB::table('course_students')
                ->where('course_id', $class->course_id)
                ->whereIn('student_id', $studentIds)
                ->update(['is_assigned' => DB::raw('true')]);

            $class->refreshIsFullStatus();

            return $class->loadCount('students');
        });
    }

    public function removeStudents(int $id, array $studentIds)
    {
        $class = ClassRoom::findOrFail($id);

        return DB::transaction(function () use ($class, $studentIds) {
            $class->students()->detach($studentIds);

            DB::table('course_students')
                ->where('course_id', $class->course_id)
                ->whereIn('student_id', $studentIds)
                ->update(['is_assigned' => DB::raw('false')]);

            $class->refreshIsFullStatus();

            return $class->loadCount('students');
        });
    }

    private function generateSessions(ClassRoom $class, array $schedules)
    {
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

        if (!empty($sessions)) {
            ClassSession::insert($sessions);
        }
    }
}
