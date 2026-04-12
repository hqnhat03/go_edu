<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Subject\CreateRequest;
use App\Http\Requests\Subject\UpdateRequest;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Str;

class SubjectService
{
    function listSubject(array $params)
    {
        $query = Subject::query();

        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['category'])) {
            $query->where('category', $params['category']);
        }
        return $query->get();
    }



    function createSubject(array $data)
    {
        try {
            return Subject::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'category' => $data['category'],
                'status' => $data['status'],
                'created_by' => auth()->id(),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException('Tên môn học đã tồn tại');
            }
            throw $e;
        }
    }

    function updateSubject(array $data, $id)
    {
        $subject = Subject::findOrFail($id);
        try {
            $subject->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'category' => $data['category'],
                'status' => $data['status'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException('Tên môn học đã tồn tại');
            }
            throw $e;
        }
        return $subject;
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        if ($subject->countCourses() > 0) {
            throw new UserException('Không thể xóa môn học đã có khóa học');
        }
        $subject->delete();
        return $subject->id;
    }
}