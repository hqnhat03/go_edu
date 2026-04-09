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

        return DB::transaction(function () use ($data) {
            // Tạo user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => 'password',
                'address' => $data['address'],
                'gender' => $data['gender'],
                'status' => $data['status'],
                'day_of_birth' => $data['day_of_birth'],
                'avatar' => $data['avatar'],
            ]);
            $user->assignRole('teacher');
            $teacher = $user->teacher()->create([
                'nationality' => $data['nationality'],
                'expertise' => $data['expertise'],
                'experience' => $data['experience'],
                'target_student_type' => $data['target_student_type'],
                'bio' => $data['bio']
            ]);

            return $user->load('teacher');
        });
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
        if (!$user->teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        return DB::transaction(function () use ($user, $data) {
            // Cập nhật bảng users
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'gender' => $data['gender'],
                'status' => $data['status'],
                'day_of_birth' => $data['day_of_birth'],
                'avatar' => $data['avatar'],
            ]);

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
        if (!$user->teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }

        return $user->load('teacher')->toArray();
    }

    function deleteTeacher($id)
    {
        $user = User::findOrFail($id);
        if (!$user->teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }
        $user->delete();
    }
}