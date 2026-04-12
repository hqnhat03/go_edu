<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseService
{
    public function listCourse(array $params)
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

    public function findById(int $id): ?Course
    {
        return Course::find($id);
    }

    public function create(array $data): Course
    {
        return Course::create($data);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);

        return $course;
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }
}