<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student_levels = Level::whereIn('slug', ['lop-6', 'lop-7', 'lop-8', 'lop-9', 'lop-10', 'lop-11', 'lop-12'])->get();
        $employee_levels = Level::whereIn('slug', ['nen-tang', 'co-ban', 'trung-cap', 'nang-cao'])->get();
        $student_subjects = Subject::whereIn('category', ['Khoa học tự nhiên', 'Khoa học xã hội'])->orWhere('name', 'Tiếng Anh')->get();
        $employee_subjects = Subject::whereNotIn('category', ['Khoa học tự nhiên', 'Khoa học xã hội'])->get();

        $student_courses = $student_levels->flatMap(function (Level $level) use ($student_subjects) {
            return $student_subjects->map(function (Subject $subject) use ($level) {
                return [
                    'name' => 'Khóa học ' . $subject->name . ' ' . $level->level . ' 2026',
                    'description' => 'Khóa học ' . $subject->name . ' ' . $level->level . ' 2026',
                    'status' => 'published',
                    'target_student' => 'student',
                    'price' => 499000,
                    'lesson_count' => 60,
                    'completion_time' => 70,
                    'image_url' => 'https://example.com/image.jpg',
                    'level_id' => $level->id,
                    'subject_id' => $subject->id,
                    'slug' => Str::slug('Khóa học ' . $subject->name . ' ' . $level->level . ' 2026')
                ];
            });
        });

        $employee_courses = $employee_levels->flatMap(function (Level $level) use ($employee_subjects) {
            return $employee_subjects->map(function (Subject $subject) use ($level) {
                return [
                    'name' => 'Khóa học ' . $subject->name . ' ' . $level->level . ' 2026',
                    'description' => 'Khóa học ' . $subject->name . ' ' . $level->level . ' 2026',
                    'status' => 'published',
                    'target_student' => 'employee',
                    'price' => 299000,
                    'lesson_count' => 12,
                    'completion_time' => 6,
                    'image_url' => 'https://example.com/image.jpg',
                    'level_id' => $level->id,
                    'subject_id' => $subject->id,
                    'slug' => Str::slug('Khóa học ' . $subject->name . ' ' . $level->level . ' 2026')
                ];
            });
        });

        $all_courses = $student_courses->concat($employee_courses);
        Course::insert($all_courses->toArray());
    }
}
