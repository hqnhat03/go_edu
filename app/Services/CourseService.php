<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Course;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Str;

class CourseService
{
    public function getList(array $params)
    {
        $query = Course::query()->join('levels', 'courses.level_id', '=', 'levels.id')
            ->join('subjects', 'courses.subject_id', '=', 'subjects.id');

        if (isset($params['name'])) {
            $query->where('courses.name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('courses.status', $params['status']);
        }

        if (isset($params['target_student'])) {
            $query->where('courses.target_student', $params['target_student']);
        }

        if (isset($params['subject'])) {
            $query->where('subjects.id', $params['subject']);
        }

        if (isset($params['level'])) {
            $query->where('levels.id', $params['level']);
        }

        return $query->select([
            'courses.id',
            'courses.name',
            'courses.status',
            'courses.target_student',
            'levels.level as level_name',
            'subjects.name as subject_name'
        ])->get();
    }

    public function findById(int $id)
    {
        $course = Course::with('level', 'subject')->findOrFail($id);
        return [
            ...$course->only([
                'id',
                'name',
                'status',
                'target_student',
                'lesson_count',
                'image_url',
                'price',
                'completion_time'
            ]),
            'level' => $course->level->level,
            'subject' => $course->subject->name,
            'class_rooms_count' => $course->classRoomsCount(),
            'course_marterials' => $course->courseMarterials()
        ];
    }

    public function create(array $data)
    {
        try {
            $course = DB::transaction(function () use ($data) {
                $course = Course::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'target_student' => $data['target_student'],
                    'price' => $data['price'],
                    'lesson_count' => $data['lesson_count'],
                    'completion_time' => $data['completion_time'],
                    'image_url' => $data['image_url'],
                    'level_id' => $data['level_id'],
                    'subject_id' => $data['subject_id'],
                ]);

                // course_marterials insert
                $course_marterials = collect($data['course_marterials'])->map(function ($value) use ($course) {
                    return [
                        'id' => $value['id'] ?? Str::uuid(),
                        'course_id' => $course->id,
                        'link_url' => $value['link_url'],
                    ];
                })->toArray();
                DB::table('course_marterials')->insert($course_marterials);
                return $course;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException("Tên khóa học đã tồn tại");
            }
            throw $e;
        }

        return [
            ...$course->only([
                'id',
                'name',
                'status',
                'image_url',
                'price',
            ]),
            'level' => $course->level->level,
            'subject' => $course->subject->name,
            'class_rooms_count' => $course->classRoomsCount()
        ];

    }

    public function update(array $data, int $id)
    {
        $course = Course::findOrFail($id);
        try {
            $course = DB::transaction(function () use ($course, $data) {
                $course->update([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'target_student' => $data['target_student'],
                    'price' => $data['price'],
                    'lesson_count' => $data['lesson_count'],
                    'completion_time' => $data['completion_time'],
                    'image_url' => $data['image_url'],
                    'level_id' => $data['level_id'],
                    'subject_id' => $data['subject_id'],
                ]);

                $course_marterials = collect($data['course_marterials'])->map(function ($value) use ($course) {
                    return [
                        'id' => $value['id'] ?? null,
                        'course_id' => $course->id,
                        'link_url' => $value['link_url'],
                    ];
                })->toArray();

                $course_marterials_ids = collect($course_marterials)->pluck('id')->toArray();

                // DELETE những cái không còn
                DB::table('course_marterials')
                    ->where('course_id', $course->id)
                    ->whereNotIn('id', $course_marterials_ids)
                    ->delete();


                // upsert
                DB::table('course_marterials')->upsert(
                    $course_marterials,
                    ['id'],
                    ['link_url']
                );
                return $course;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException("Tên khóa học đã tồn tại");
            }
            throw $e;
        }

        return [
            ...$course->only([
                'id',
                'name',
                'status',
                'target_student',
                'lesson_count',
                'image_url',
                'price',
                'completion_time'
            ]),
            'level' => $course->level->level,
            'subject' => $course->subject->name,
            'class_rooms_count' => $course->classRoomsCount(),
            'course_marterials' => $course->courseMarterials()
        ];
    }

    public function delete(int $id)
    {
        $course = Course::findOrFail($id);
        // Kiem tra dieu kien xoa

        $course->delete();
        return $course->id;
    }
}