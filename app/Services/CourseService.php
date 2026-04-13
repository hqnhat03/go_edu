<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Course;
use App\Models\CourseMaterial;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Str;

class CourseService
{
    public function getList(array $params)
    {
        return Course::query()
            ->with(['level', 'subject'])
            // Maintain inner join behavior if needed, otherwise remove has()
            ->has('level')
            ->has('subject')
            ->when($params['name'] ?? null, fn($q, $name) => $q->where('name', 'like', "%{$name}%"))
            ->when($params['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($params['target_student'] ?? null, fn($q, $target) => $q->where('target_student', $target))
            ->when($params['subject'] ?? null, fn($q, $subjectId) => $q->where('subject_id', $subjectId))
            ->when($params['level'] ?? null, fn($q, $levelId) => $q->where('level_id', $levelId))
            ->get()
            ->map(fn($course) => [
                'id' => $course->id,
                'name' => $course->name,
                'status' => $course->status,
                'target_student' => $course->target_student,
                'level_name' => $course->level->level,
                'subject_name' => $course->subject->name,
            ]);
    }

    public function findById(int $id)
    {
        $course = Course::with(['level', 'subject', 'materials'])
            ->withCount('classRooms')
            ->findOrFail($id);

        return $this->transform($course, true);
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

                if (!empty($data['course_marterials'])) {
                    $course->materials()->createMany(
                        collect($data['course_marterials'])->map(fn($m) => [
                            'id' => $m['id'] ?? (string) Str::uuid(),
                            'link_url' => $m['link_url'],
                        ])->toArray()
                    );
                }

                return $course;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException("Tên khóa học đã tồn tại");
            }
            throw $e;
        }

        return $this->transform($course->load(['level', 'subject'])->loadCount('classRooms'));
    }

    public function update(array $data, int $id)
    {
        $course = Course::findOrFail($id);
        try {
            $course = DB::transaction(function () use ($course, $data) {
                $course->update([
                    'name' => $data['name'] ?? $course->name,
                    'slug' => isset($data['name']) ? Str::slug($data['name']) : $course->slug,
                    'description' => $data['description'] ?? $course->description,
                    'status' => $data['status'] ?? $course->status,
                    'target_student' => $data['target_student'] ?? $course->target_student,
                    'price' => $data['price'] ?? $course->price,
                    'lesson_count' => $data['lesson_count'] ?? $course->lesson_count,
                    'completion_time' => $data['completion_time'] ?? $course->completion_time,
                    'image_url' => $data['image_url'] ?? $course->image_url,
                    'level_id' => $data['level_id'] ?? $course->level_id,
                    'subject_id' => $data['subject_id'] ?? $course->subject_id,
                ]);

                if (isset($data['course_marterials'])) {
                    $materials = collect($data['course_marterials'])->map(fn($m) => [
                        'id' => $m['id'] ?? null,
                        'course_id' => $course->id,
                        'link_url' => $m['link_url'],
                    ]);

                    $course->materials()
                        ->whereNotIn('id', $materials->pluck('id')->filter())
                        ->delete();

                    CourseMaterial::upsert($materials->filter(fn($m) => $m['id'])->toArray(), ['id'], ['link_url']);
                }

                return $course;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException("Tên khóa học đã tồn tại");
            }
            throw $e;
        }

        return $this->transform($course->load(['level', 'subject', 'materials'])->loadCount('classRooms'), true);
    }

    public function delete(int $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return $course->id;
    }

    /**
     * Helper to unify response format
     */
    private function transform(Course $course, bool $full = false): array
    {
        $data = [
            'id' => $course->id,
            'name' => $course->name,
            'status' => $course->status,
            'image_url' => $course->image_url,
            'price' => $course->price,
            'level' => $course->level?->level,
            'subject' => $course->subject?->name,
            'class_rooms_count' => $course->class_rooms_count ?? 0,
        ];

        if ($full) {
            $data = array_merge($data, [
                'target_student' => $course->target_student,
                'lesson_count' => $course->lesson_count,
                'completion_time' => $course->completion_time,
                'course_marterials' => $course->materials ?? [],
            ]);
        }

        return $data;
    }
}