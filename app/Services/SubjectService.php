<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Subject\CreateRequest;
use App\Http\Requests\Subject\UpdateRequest;
use App\Models\Subject;
use Illuminate\Database\QueryException;

class SubjectService
{
    function listSubject()
    {
        return Subject::query()
            ->whereIn("status", ["published", "archived"])
            ->orWhere("created_by", auth()->id())
            ->get(["id", "name", "category", "training_level", "student_type", "status"]);
    }

    function createSubject(CreateRequest $request)
    {
        try {
            $data = $request->validated();
            return Subject::create([
                "name" => $data["name"],
                "category" => $data["category"],
                "training_level" => $data["training_level"],
                "student_type" => $data["student_type"],
                "status" => $data["status"],
                "created_by" => auth()->id(),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Tên môn học và trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }
    }

    function updateSubject(UpdateRequest $request, $id)
    {
        $data = $request->validated();
        try {
            $subject = Subject::findOrFail($id);
            $subject->update([
                "name" => $data["name"],
                "category" => $data["category"],
                "training_level" => $data["training_level"],
                "student_type" => $data["student_type"],
                "status" => $data["status"],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Tên môn học và trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }
        return $subject;
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
    }
}