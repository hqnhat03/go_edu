<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    public function __construct(protected MailService $mailService)
    {
    }

    public function createStudent(array $data)
    {
        $password = Str::random(10);
        try {
            $user = DB::transaction(function () use ($data, $password) {

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $password,
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['date_of_birth'],
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
            if ($e->getCode() === '23505' || $e->getCode() === '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == '1062')) {
                throw new UserException("Email đã tồn tại");
            }
            throw $e;
        }

        $this->mailService->sendStudentAccountCreatedInfo($user, $password);

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

    public function listStudent(array $params)
    {
        $query = Student::query()
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if (isset($params['q'])) {
            $query->whereHas('user', function ($q) use ($params) {
                $q->where('name', 'like', '%' . $params['q'] . '%')
                    ->orWhere('email', 'like', '%' . $params['q'] . '%');
            });
        }

        if (isset($params['status'])) {
            $query->whereHas('user', function ($q) use ($params) {
                $q->where('status', $params['status']);
            });
        }

        if (isset($params['student_type'])) {
            $query->where('student_type', $params['student_type']);
        }

        $limit = $params['limit'] ?? 10;
        $students = $query->paginate($limit);

        $students->getCollection()->transform(function ($student) {
            $user = $student->user;
            return [
                'id' => $student->id,
                'student_type' => $student->student_type,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'status' => $user->status ?? null,
                'avatar' => $user->avatar ?? null,
                'created_at' => $student->created_at,
            ];
        });

        return $students;
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
                    'date_of_birth' => $data['date_of_birth'],
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
        $name = $request->query('search');
        $students = Student::query()
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select('students.id', 'users.name');

        // ❗ bắt buộc phải có keyword
        if (empty($name)) {
            return response()->json([]);
        }

        $students->where('users.name', 'like', '%' . $name . '%');

        // keyword ngắn → limit ít
        if (mb_strlen($name) < 3) {
            $students->limit(20);
        } else {
            $students->limit(50); // vẫn phải limit
        }

        return $students->get();
    }
}