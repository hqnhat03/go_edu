<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Student\CreateRequest;
use App\Http\Requests\Student\UpdateRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function createStudent(CreateRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => 'password123',
            'phone' => $data['phone'],
            'address' => $data['address'],
            'gender' => $data['gender'],
            'status' => $data['status'],
            'date_of_birth' => $data['day_of_birth'],
            'avatar' => $data['avatar'],
        ]);
        $user->assignRole('student');
        $user->student()->create([
            'student_type' => $data['student_type'],
            'school' => $data['school'],
            'grade' => $data['grade'],
            'work' => $data['work'],
            'position' => $data['position'],
        ]);
        return $user->load('student')->toArray();
    }

    public function listStudent()
    {
        $data = User::whereHas('student')->get();
        return $data;
    }

    public function updateStudent(UpdateRequest $request, $id)
    {
        $data = $request->validated();
        $user = User::findOrFail($id);
        if (!$user->student) {
            throw new UserException('Không tìm thấy học sinh');
        }
        return DB::transaction(function () use ($user, $data) {
            // Cập nhật bảng users
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'gender' => $data['gender'],
                'status' => $data['status'],
                'date_of_birth' => $data['day_of_birth'],
                'avatar' => $data['avatar'],
            ]);

            // Cập nhật bảng teachers (thông qua relationship)
            // Lưu ý: Đảm bảo $data chứa các trường của bảng teacher
            $user->student()->update([
                'student_type' => $data['student_type'],
                'school' => $data['school'],
                'grade' => $data['grade'],
                'work' => $data['work'],
                'position' => $data['position'],
            ]);

            return $user->load('student');
        });
    }

    public function getStudent($id)
    {

        $user = User::findOrFail($id);
        if (!$user->student) {
            throw new UserException('Không tìm thấy học sinh');
        }
        return $user->load('student');
    }

    public function deleteStudent($id)
    {
        $user = User::findOrFail($id);
        if (!$user->student) {
            throw new UserException('Không tìm thấy học sinh');
        }
        $user->delete();
    }

    public function getAllStudent(Request $request)
    {
        $status = $request->query('status');
        $data = User::query()
            ->whereHas('student')
            ->with(['student:id,user_id'])
            ->select('id', 'email', 'name', 'status');
        if ($status) {
            $data = $data->where('status', $status);
        }
        return $data->get()->map(function ($user) {
            return [
                'email' => $user->email,
                'name' => $user->name,
                'student_id' => $user->student->id,
            ];
        });
    }
}