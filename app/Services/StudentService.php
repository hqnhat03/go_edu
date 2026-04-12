<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function createStudent(array $data)
    {
        try {
            $user = DB::transaction(function () use ($data) {

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password123'),
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

                return $user; // ✅ phải return trong transaction
            });

        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new UserException("Email đã tồn tại");
            }
            throw $e;
        }
        return [
            ...$user->only([
                'name',
                'email',
                'phone',
                'status',
                'avatar'
            ]),
            ...$user->student->only([
                'id',
                'student_type',
            ])
        ];


    }

    public function listStudent(array $param)
    {
        $student = Student::query()->join('users', 'users.id', '=', 'students.user_id');

        if (isset($param['q'])) {
            $student->where('users.name', 'like', '%' . $param['q'] . '%')
                ->orWhere('users.email', 'like', '%' . $param['q'] . '%');
        }

        if (isset($param['status'])) {
            $student->where('users.status', $param['status']);
        }

        if (isset($param['student_type'])) {
            $student->where('student_type', $param['student_type']);
        }

        return $student->select([
            'students.id',
            'students.student_type',
            'users.name',
            'users.email',
            'users.phone',
            'users.status',
            'users.avatar'
        ])->get();
    }

    public function updateStudent(array $data, $id)
    {
        $student = Student::with('user')->findOrFail($id);
        if (!$student->user) {
            throw new UserException('Không tìm thấy học sinh');
        }
        try {
            $student = DB::transaction(function () use ($student, $data) {
                $student->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password123'),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['day_of_birth'],
                    'avatar' => $data['avatar'],
                ]);

                $student->update([
                    'student_type' => $data['student_type'],
                    'school' => $data['school'],
                    'grade' => $data['grade'],
                    'work' => $data['work'],
                    'position' => $data['position'],
                ]);

                return $student;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new UserException("Email đã tồn tại");
            }
            throw $e;
        }

        return [
            ...$student->only([
                'id',
                'student_type',
                'school',
                'grade',
                'work',
                'position'
            ]),
            ...$student->user->only([
                'name',
                'email',
                'gender',
                'status',
                'phone',
                'date_of_birth',
                'address',
                'avatar'
            ])
        ];
    }

    public function getStudent($id)
    {

        $student = Student::with('user')->findOrFail($id);
        if (!$student) {
            throw new UserException('Không tìm thấy học sinh');
        }
        return [
            ...$student->only([
                'id',
                'student_type',
                'school',
                'grade',
                'work',
                'position'
            ]),
            ...$student->user->only([
                'name',
                'email',
                'gender',
                'status',
                'phone',
                'date_of_birth',
                'address',
                'avatar'
            ])
        ];
    }

    public function deleteStudent($id)
    {
        $student = Student::with('user')->findOrFail($id);
        if (!$student) {
            throw new UserException('Không tìm thấy học sinh');
        }
        $student->user->delete();
        return $student->id;
    }

    public function getAllStudent(Request $request)
    {
        $status = $request->query('status');
        $students = Student::query()->join('users', 'users.id', '=', 'students.user_id')
            ->select('students.id', 'users.email', 'users.name', 'users.status');
        if ($status) {
            $students = $students->where('users.status', $status);
        }
        return $students->get();
    }
}