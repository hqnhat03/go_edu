<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Teacher\CreateRequest;
use App\Http\Requests\Teacher\UpdateRequest;
use App\Models\Teacher;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class TeacherService
{
    function createTeacher(CreateRequest $request)
    {
        $data = $request->validated();
        $user = User::create($data);
        $user->assignRole('teacher');
        $teacher = $user->teacher()->create([
            'nationality' => $data['nationality'],
            'expertise' => $data['expertise'],
            'experience' => $data['experience'],
            'target_student_type' => $data['target_student_type'],
            'bio' => $data['bio']
        ]);
        return array_merge($user->toArray(), $teacher->toArray());
    }

    function listTeacher()
    {
        $data = User::whereHas('teacher')->get();
        return $data;
    }

    function updateTeacher(UpdateRequest $request, $id)
    {
        $data = $request->validated();
        $user = User::findOrFail($id);

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        return DB::transaction(function () use ($user, $data) {
            // Cập nhật bảng users
            $user->update($data);

            // Cập nhật bảng teachers (thông qua relationship)
            // Lưu ý: Đảm bảo $data chứa các trường của bảng teacher
            $user->teacher()->update(collect($data)->only([
                'nationality',
                'expertise',
                'experience',
                'target_student_type'
            ])->toArray());

            return $user->load('teacher');
        });
    }

    function getTeacher($id)
    {
        $user = User::findOrFail($id);

        return $user->load('teacher')->toArray();
    }

    function deleteTeacher($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}