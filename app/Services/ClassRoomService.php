<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\ClassRoom;
use DB;
use Illuminate\Database\QueryException;
use Str;

class ClassRoomService
{
    public function getList(array $params)
    {
        $query = ClassRoom::query()->join('courses', 'class_rooms.course_id', '=', 'courses.id')
            ->join('class_teachers', 'class_rooms.id', '=', 'class_teachers.class_id')
            ->leftJoin('class_students', 'class_rooms.id', '=', 'class_students.class_id')
            ->leftJoin('teachers', 'class_teachers.teacher_id', '=', 'teachers.id')
            ->join('users', 'teachers.user_id', '=', 'users.id');

        if (isset($params['course_id'])) {
            $query->where('class_rooms.course_id', $params['course_id']);
        }

        if (isset($params['start_day'])) {
            $query->where('class_rooms.start_day', '>=', $params['start_day']);
        }
        if (isset($params['end_day'])) {
            $query->where('class_rooms.end_day', '<=', $params['end_day']);
        }

        if (isset($params['class_code'])) {
            $query->where('class_rooms.class_code', 'like', '%' . $params['class_code'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('class_rooms.status', $params['status']);
        }

        if (isset($params['teacher_name'])) {
            $query->where('users.name', 'like', '%' . $params['teacher_name'] . '%');
        }

        $data = $query->select([
            'class_rooms.id',
            'class_rooms.class_code',
            'class_rooms.start_day',
            'class_rooms.end_day',
            'class_rooms.status',
            'users.name as teacher_name',
            'class_students.student_id',
            'teachers.id as teacher_id',
        ])->get();

        $result = $data->groupBy('id')->map(function ($items) {
            $class = $items->first();

            return [
                'id' => $class->id,
                'class_code' => $class->class_code,
                'start_day' => $class->start_day,
                'end_day' => $class->end_day,
                'status' => $class->status,
                'teachers' => $items->map(function ($item) {
                    return [
                        'id' => $item->teacher_id,
                        'name' => $item->teacher_name,
                    ];
                })->unique()->values(),
                'student_count' => $items->pluck('student_id')->unique()->count(),
            ];
        })->values();

        return $result;
    }

    public function findById(int $id)
    {
        $class = ClassRoom::with(['course', 'teachers'])->findOrFail($id);
        return [
            ...$class->only('id', 'class_code', 'start_day', 'end_day', 'max_student', 'meeting_url', 'status'),
            'teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'avatar' => $teacher->user->avatar,
                ];
            }),
            'class_teaches' => DB::table('class_teachers')->where('class_id', $class->id)
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

                // class_teachers insert
                $class_teachers = collect($data['class_teachers'])->map(function ($value) use ($class) {
                    return [
                        'id' => $value['id'] ?? Str::uuid(),
                        'class_id' => $class->id,
                        'teacher_id' => $value['teacher_id'],
                    ];
                })->toArray();
                DB::table('class_teachers')->insert($class_teachers);
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
            'teachers' => $class->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                ];
            }),
            'student_count' => $class->students->count(),
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
                DB::table('class_teachers')
                    ->where('class_id', $class->id)
                    ->whereNotIn('id', $class_teachers_ids)
                    ->delete();

                // upsert
                DB::table('class_teachers')->upsert(
                    $class_teachers,
                    ['id'],
                    ['teacher_id']
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
            'class_teaches' => DB::table('class_teachers')->where('class_id', $class->id)
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
        ];
    }
    public function delete($id)
    {
        $class = ClassRoom::findOrFail($id);
        // kiem tra du lieu

        $class->delete();
        return $class->id;
    }
}