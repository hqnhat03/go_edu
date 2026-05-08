<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Teacher\CreateRequest;
use App\Http\Requests\Teacher\UpdateRequest;
use App\Models\Teacher;
use App\Models\User;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherService
{
    public function __construct(protected MailService $mailService)
    {
    }
    function createTeacher(array $data)
    {
        $password = Str::random(10);

        try {
            $teacher = DB::transaction(function () use ($data, $password) {
                // Tạo user
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => $password,
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['date_of_birth'],
                    'avatar' => $data['avatar'],
                ]);

                $user->assignRole('teacher');

                return $user->teacher()->create([
                    'nationality' => $data['nationality'],
                    'expertise' => $data['expertise'],
                    'experience' => $data['experience'],
                    'target_student' => $data['target_student'],
                    'bio' => $data['bio']
                ]);
            });
        } catch (QueryException $e) {
            // 23505: Postgres unique violation, 1062: MySQL unique violation
            if ($e->getCode() == '23505' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == '1062')) {
                throw new UserException('Email đã tồn tại');
            }
            throw $e;
        }

        $this->mailService->sendTeacherAccountCreatedInfo($teacher->user, $password);

        return [
            ...$teacher->only([
                'id',
                'expertise',
                'target_student',
            ]),
            ...$teacher->user->only([
                'name',
                'email',
                'phone',
                'status',
                'avatar'
            ])
        ];
    }

    function listTeacher(array $param)
    {
        $query = Teacher::query()->join('users', 'users.id', '=', 'teachers.user_id');

        if (isset($param['search'])) {
            $query->where(function ($q) use ($param) {
                $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$param['search']}%"])
                    ->orWhere('users.email', 'like', '%' . $param['search'] . '%');
            });
        }
        if (isset($param['status']) && $param['status'] !== 'all') {
            $query->where('users.status', $param['status']);
        }
        if (isset($param['expertise'])) {
            $query->where('expertise', 'like', '%' . $param['expertise'] . '%');
        }

        // Sorting
        $sortBy = $param['sort_by'] ?? 'created_at';
        $sortOrder = $param['sort_order'] ?? 'desc';

        $allowedSortFields = ['id', 'name', 'email', 'phone', 'status', 'created_at', 'expertise', 'target_student'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            if (in_array($sortBy, ['name', 'email', 'phone', 'status'])) {
                $query->orderBy("users.$sortBy", $sortOrder);
            } elseif ($sortBy === 'created_at') {
                $query->orderBy("users.created_at", $sortOrder);
            } else {
                $query->orderBy("teachers.$sortBy", $sortOrder);
            }
        } else {
            $query->latest('users.created_at');
        }

        $limit = $param['limit'] ?? 10;

        return $query->select([
            'teachers.id',
            'teachers.expertise',
            'teachers.target_student',
            'users.name',
            'users.email',
            'users.phone',
            'users.status',
            'users.avatar'
        ])->paginate($limit);
    }

    function updateTeacher(array $data, $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        if (!$teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        $teacher = DB::transaction(function () use ($teacher, $data) {
            // Cập nhật bảng users
            $teacher->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'gender' => $data['gender'],
                'status' => $data['status'],
                'date_of_birth' => $data['date_of_birth'],
                'avatar' => $data['avatar'],
            ]);

            $teacher->update([
                'nationality' => $data['nationality'],
                'expertise' => $data['expertise'],
                'experience' => $data['experience'],
                'target_student' => $data['target_student'],
                'bio' => $data['bio']
            ]);

            return $teacher;
        });

        return [
            ...$teacher->only([
                'id',
                'expertise',
                'experience',
                'target_student',
            ]),
            ...$teacher->user->only([
                'name',
                'email',
                'phone',
                'status',
                'avatar',
                'address',
                'nationality',
                'date_of_birth',
                'gender',
                'bio'
            ])
        ];
    }

    function getTeacher($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        if (!$teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }

        return [
            ...$teacher->only([
                'id',
                'expertise',
                'experience',
                'target_student',
            ]),
            ...$teacher->user->only([
                'name',
                'email',
                'phone',
                'status',
                'avatar',
                'address',
                'nationality',
                'date_of_birth',
                'gender',
                'bio'
            ])
        ];
    }

    function deleteTeacher($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        if (!$teacher) {
            throw new UserException('Không tìm thấy giáo viên');
        }
        $teacher->user->delete();
        return $teacher->id;
    }
}