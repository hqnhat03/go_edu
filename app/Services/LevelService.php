<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Helpers\StringHelper;
use App\Http\Requests\Level\CreateRequest;
use App\Http\Requests\Level\UpdateRequest;
use App\Models\Level;
use Illuminate\Database\QueryException;

class LevelService
{
    public function listLevel()
    {
        $levels = Level::query()
            ->whereIn("status", ["published", "archived"])
            ->orWhere("created_by", auth()->id())
            ->get(["id", "subject_id", "level", "status"])->load("subject:id,name,training_level,student_type");

        return $levels->map(function ($level) {
            return [
                'id' => $level->id,
                'subject_id' => $level->subject_id,
                'level' => $level->level,
                'subject_name' => $level->subject->name,
                'training_level' => $level->subject->training_level,
                'student_type' => $level->subject->student_type,
                'status' => $level->status
            ];
        });
    }

    public function createLevel(CreateRequest $request)
    {
        try {
            $data = $request->validated();
            $level = StringHelper::uppercaseFirst($data["level"]);
            $level_normalized = StringHelper::normalizeString($data["level"]);
            $level = Level::create([
                "subject_id" => $data["subject_id"],
                "level" => $level,
                "level_normalized" => $level_normalized,
                "status" => $data["status"],
                "created_by" => auth()->id(),
            ])->load("subject:id,name,training_level,student_type");
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Tên môn học và trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }
        return [
            'id' => $level->id,
            'subject_id' => $level->subject_id,
            'level' => $level->level,
            'subject_name' => $level->subject->name,
            'training_level' => $level->subject->training_level,
            'student_type' => $level->subject->student_type,
            'status' => $level->status
        ];
    }

    public function updateLevel(UpdateRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $level = Level::findOrFail($id);
            $level_name = StringHelper::uppercaseFirst($data["level"]);
            $level_normalized = StringHelper::normalizeString($data["level"]);
            $level->update([
                "subject_id" => $data["subject_id"],
                "level" => $level_name,
                "level_normalized" => $level_normalized,
                "status" => $data["status"],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Tên môn học và trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }

        return [
            'id' => $level->id,
            'subject_id' => $level->subject_id,
            'level' => $level->level,
            'subject_name' => $level->subject->name,
            'training_level' => $level->subject->training_level,
            'student_type' => $level->subject->student_type,
            'status' => $level->status
        ];
    }

    public function deleteLevel($id)
    {
        $level = Level::findOrFail($id);
        $level->delete();
        return $level->id;
    }
}