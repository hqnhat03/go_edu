<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function index(request $request)
    {
        $type = $request->type;
        return match ($type) {
            'course' => $this->courseFilters(),
            'student' => $this->studentFilters(),
            'teacher' => $this->teacherFilters(),
            'subject' => $this->subjectFilters(),
            default => [],
        };
    }

    private function subjectFilters()
    {
        return [
            'status' => ['draft', 'published', 'archived'],
            'category' => Subject::query()->select('category')->distinct()->pluck('category'),
        ];
    }

    private function courseFilters()
    {
        return [
            'education_levels' => $this->getEducationLevels(),
            'levels' => $this->getLevels(),
            'subjects' => $this->getSubjects(),
        ];
    }

    private function getEducationLevels()
    {
        return Level::query()
            ->select('education_level')
            ->distinct()
            ->pluck('education_level');
    }

    private function getLevels()
    {
        return Level::query()
            ->select('id', 'level')
            ->get();
    }

    private function getSubjects()
    {
        return Subject::query()
            ->select('id', 'name')
            ->get();
    }

    private function studentFilters()
    {
        return [
            'status' => ['active', 'inactive'],
            'target_student' => ['student', 'employee'],
        ];
    }

    private function teacherFilters()
    {
        return [
            'status' => ['active', 'inactive'],
            'target_student' => ['student', 'employee', 'all'],
            'gender' => ['male', 'female', 'other'],
        ];
    }
}
