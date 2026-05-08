<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Guardian\CreateRequest;
use App\Http\Requests\Guardian\UpdateRequest;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class GuardianService
{
    public function __construct(protected MailService $mailService)
    {
    }

    public function createGuardian(array $data)
    {
        $password = Str::random(10);
        try {
            $guardian = DB::transaction(function () use ($data, $password) {
                $user = User::create([
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "password" => $password,
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['date_of_birth'],
                    'avatar' => $data['avatar'],
                ]);
                $user->assignRole('guardian');
                $guardian = $user->guardian()->create();
                $guardian->students()->sync($data['student_ids']);
                return $guardian;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23505' || $e->getCode() === '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == '1062')) {
                throw new UserException("Email đã tồn tại");
            }
            if ($e->errorInfo[1] == '1452') {
                throw new UserException("Học sinh không tồn tại");
            }
            throw $e;
        }

        $this->mailService->sendGuardianAccountCreatedInfo($guardian->user, $password);

        return [
            ...$guardian->only(['id']),
            ...$guardian->user->only(['name', 'email', 'phone', 'status', 'avatar', 'gender', 'address']),
            'date_of_birth' => $guardian->user->date_of_birth,
            'students' => $guardian->students->map(function ($s) {
                return [
                    'student_id' => $s->id,
                    'name' => $s->user->name,
                ];
            })->values()
        ];
    }

    public function listGuardian(array $params)
    {
        $query = Guardian::query()->join('users', 'users.id', '=', 'guardians.user_id');

        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->whereRaw('unaccent(users.name) ilike unaccent(?)', ["%{$params['search']}%"])
                    ->orWhere('users.email', 'like', '%' . $params['search'] . '%')
                    ->orWhere('users.phone', 'like', '%' . $params['search'] . '%');
            });
        }

        if (isset($params['status']) && $params['status'] !== 'all') {
            $query->where('users.status', $params['status']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';

        $allowedSortFields = ['id', 'name', 'email', 'phone', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            if (in_array($sortBy, ['name', 'email', 'phone', 'status', 'created_at'])) {
                $query->orderBy("users.$sortBy", $sortOrder);
            } else {
                $query->orderBy("guardians.$sortBy", $sortOrder);
            }
        } else {
            $query->latest('users.created_at');
        }

        $limit = $params['limit'] ?? 10;

        return $query->select([
            'guardians.id',
            'users.name',
            'users.email',
            'users.phone',
            'users.status',
            'users.avatar',
            'users.gender',
            'users.address',
            'users.date_of_birth',
        ])->with('students.user')->paginate($limit);
    }

    public function updateGuardian(array $data, $id)
    {
        $guardian = Guardian::with('user', 'students')->findOrFail($id);
        try {
            $guardian = DB::transaction(function () use ($guardian, $data) {
                $guardian->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['date_of_birth'],
                    'avatar' => $data['avatar'],
                ]);
                $guardian->students()->sync($data['student_ids']);
                return $guardian;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1452') {
                throw new UserException("Học sinh không tồn tại");
            }
            if ($e->errorInfo[1] == '1062') {
                throw new UserException("Email đã tồn tại");
            }
            throw $e;
        }
        return [
            ...$guardian->only('id'),
            ...$guardian->user->only('name', 'email', 'phone', 'status', 'avatar', 'gender', 'address'),
            'date_of_birth' => $guardian->user->date_of_birth,
            'students' => $guardian->students->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->user->name,
                ];
            })->values()
        ];
    }

    public function getGuardian($id)
    {
        $guardian = Guardian::with('user', 'students')->findOrFail($id);
        return [
            ...$guardian->only('id'),
            ...$guardian->user->only('name', 'email', 'phone', 'status', 'avatar', 'gender', 'address'),
            'date_of_birth' => $guardian->user->date_of_birth,
            'students' => $guardian->students->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->user->name,
                ];
            })->values()
        ];

    }

    public function deleteGuardian($id)
    {
        $guardian = Guardian::with('user')->findOrFail($id);
        $guardian->user->delete();
        return $guardian->id;
    }

}