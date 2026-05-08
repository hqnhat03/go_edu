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
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select([
                'students.*',
                'users.name',
                'users.email',
                'users.phone',
                'users.status',
                'users.avatar',
                'users.created_at as user_created_at'
            ]);

        if (isset($params['q'])) {
            $query->where(function ($q) use ($params) {
                $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$params['q']}%"])
                    ->orWhere('users.email', 'like', '%' . $params['q'] . '%')
                    ->orWhere('users.phone', 'like', '%' . $params['q'] . '%');
            });
        }

        if (isset($params['status']) && $params['status'] !== 'all') {
            $query->where('users.status', $params['status']);
        }

        if (isset($params['student_type']) && $params['student_type'] !== 'all') {
            $query->where('students.student_type', $params['student_type']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';

        $allowedSortFields = ['id', 'name', 'email', 'phone', 'status', 'created_at', 'student_type'];

        if (in_array($sortBy, $allowedSortFields)) {
            if (in_array($sortBy, ['name', 'email', 'phone', 'status'])) {
                $query->orderBy("users.$sortBy", $sortOrder);
            } elseif ($sortBy === 'created_at') {
                $query->orderBy("students.created_at", $sortOrder);
            } else {
                $query->orderBy("students.$sortBy", $sortOrder);
            }
        } else {
            $query->orderBy('students.created_at', 'desc');
        }

        $limit = $params['limit'] ?? 10;
        $students = $query->paginate($limit);

        $students->getCollection()->transform(function ($student) {
            return [
                'id' => $student->id,
                'student_type' => $student->student_type,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'status' => $student->status,
                'avatar' => $student->avatar,
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

        $students->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$name}%"]);

        // keyword ngắn → limit ít
        if (mb_strlen($name) < 3) {
            $students->limit(20);
        } else {
            $students->limit(50); // vẫn phải limit
        }

        return $students->get();
    }
}