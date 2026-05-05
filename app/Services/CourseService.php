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

        if (isset($params['education_level'])) {
            $query->where('levels.education_level', $params['education_level']);
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
        $course = Course::with(['level', 'subject', 'materials'])
            ->withCount(['classRooms', 'students as student_count'])
            ->findOrFail($id);

        return $this->transform($course, true);
    }

    public function getPublicList(array $params)
    {
        $query = Course::query()
            ->with(['classRooms.teachers.user'])
            ->withCount('classRooms')
            ->where('status', 'published');

        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['target_student'])) {
            $query->where('target_student', $params['target_student']);
        }

        if (isset($params['subject_id'])) {
            $query->where('subject_id', $params['subject_id']);
        }

        if (isset($params['level_id'])) {
            $query->where('level_id', $params['level_id']);
        }

        if (isset($params['education_level'])) {
            $query->whereHas('level', function ($q) use ($params) {
                $q->where('education_level', $params['education_level']);
            });
        }

        $limit = $params['limit'] ?? 10;
        $courses = $query->paginate($limit);

        $courses->getCollection()->transform(function ($course) {
            $teachers = collect();
            if ($course->relationLoaded('classRooms')) {
                foreach ($course->classRooms as $classRoom) {
                    foreach ($classRoom->teachers as $teacher) {
                        if ($teacher->user) {
                            $teachers->push([
                                'id' => $teacher->id,
                                'name' => $teacher->user->name,
                                'avatar' => $teacher->user->avatar,
                            ]);
                        }
                    }
                }
                $course->setAttribute('teachers', $teachers->unique('id')->values()->all());
                $course->makeHidden('classRooms');
            }
            return $course;
        });

        return $courses;
    }

    public function findPublicBySlug($slug)
    {
        $course = Course::with(['subject', 'classRooms.teachers.user', 'classRooms.schedules'])
            ->withCount('classRooms')
            ->withCount([
                'classRooms as enrolled_students_count' => function ($q) {
                    $q->join('class_students', 'class_rooms.id', '=', 'class_students.class_id');
                }
            ])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        // Map từng lớp: kèm teachers và schedules riêng
        $classRooms = $course->classRooms->map(function ($classRoom) {
            $teachers = $classRoom->teachers
                ->filter(fn($t) => $t->user)
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->user->name,
                    'avatar' => $t->user->avatar,
                ])->values();

            $schedules = $classRoom->schedules
                ->sortBy('day_of_week')
                ->map(fn($s) => [
                    'day_of_week' => $s->day_of_week,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                ])->values();

            return [
                'id' => $classRoom->id,
                'class_code' => $classRoom->class_code,
                'start_day' => $classRoom->start_day,
                'end_day' => $classRoom->end_day,
                'status' => $classRoom->status,
                'teachers' => $teachers,
                'schedules' => $schedules,
            ];
        });

        $course->setAttribute('class_rooms', $classRooms);

        // Làm gọn subject: chỉ trả name và category
        $course->setAttribute('subject', [
            'name' => $course->subject?->name,
            'category' => $course->subject?->category,
        ]);

        $course->makeHidden('classRooms');

        return $course;
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

                if (!empty($data['course_materials'])) {
                    $course->materials()->createMany(
                        collect($data['course_materials'])->map(fn($m) => [
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

        return $this->transform($course->load(['level', 'subject'])->loadCount(['classRooms', 'students as student_count']));
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

        return $this->transform($course->load(['level', 'subject', 'materials'])->loadCount(['classRooms', 'students as student_count']), true);
    }

    public function delete(int $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return $course->id;
    }

    public function getStudents(int $id, array $params)
    {
        $course = Course::findOrFail($id);
        $query = $course->students()->join('users', 'students.user_id', '=', 'users.id');

        if (isset($params['is_assigned'])) {
            $isAssigned = filter_var($params['is_assigned'], FILTER_VALIDATE_BOOLEAN);
            $query->wherePivot('is_assigned', DB::raw($isAssigned ? 'true' : 'false'));
        }

        return $query->select([
            'students.id',
            'users.name',
            'users.email',
            'users.phone',
            'users.avatar',
            'course_students.is_assigned',
            DB::raw("(SELECT cr.class_code 
                      FROM class_rooms cr
                      JOIN class_students cs ON cr.id = cs.class_id 
                      WHERE cs.student_id = students.id 
                      AND cr.course_id = $id 
                      LIMIT 1) as class_code")
        ])->get();
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
            'student_count' => $course->student_count ?? 0,
        ];

        if ($full) {
            $data = array_merge($data, [
                'target_student' => $course->target_student,
                'lesson_count' => $course->lesson_count,
                'completion_time' => $course->completion_time,
                'course_materials' => $course->materials ?? [],
            ]);
        }

        return $data;
    }
}