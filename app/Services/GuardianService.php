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

    public function createGuardian(array $data)
    {
        try {
            $guardian = DB::transaction(function () use ($data) {
                $password = Str::random(8);
                $user = User::create([
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "password" => $password,
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'status' => $data['status'],
                    'date_of_birth' => $data['day_of_birth'],
                    'avatar' => $data['avatar'],
                ]);
                $user->assignRole('guardian');
                $guardian = $user->guardian()->create();
                $guardian->students()->sync($data['student_ids']);
                return $guardian;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1452') {
                throw new UserException("Học sinh không tồn tại");
            }
            throw $e;
        }
        return [
            ...$guardian->only(['id']),
            ...$guardian->user->only(['name', 'email', 'phone', 'status', 'avatar']),
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
        $query = Guardian::query()->join('users', 'guardians.user_id', '=', 'users.id')
            ->join('student_guardians', 'guardians.id', '=', 'student_guardians.guardian_id')
            ->join('students', 'student_guardians.student_id', '=', 'students.id');

        if (isset($params['q'])) {
            $query->where('users.name', 'like', '%' . $params['q'] . '%')
                ->orWhere('users.email', 'like', '%' . $params['q'] . '%')
                ->orWhere('phone', 'like', '%' . $params['q'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('users.status', $params['status']);
        }

        return $query->get()->map(function ($guardian) {
            return [
                'id' => $guardian->id,
                'name' => $guardian->user->name,
                'email' => $guardian->user->email,
                'phone' => $guardian->user->phone,
                'status' => $guardian->user->status,
                'avatar' => $guardian->user->avatar,
                'students' => $guardian->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name,
                        'email' => $student->user->email,
                    ];
                })
            ];
        });
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
                    'date_of_birth' => $data['day_of_birth'],
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
            ...$guardian->user->only('name', 'email', 'phone', 'status', 'avatar'),
            'students' => $guardian->students->map(function ($s) {
                return [
                    'student_id' => $s->id,
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
            ...$guardian->user->only('name', 'email', 'phone', 'status', 'avatar'),
            'students' => $guardian->students->map(function ($s) {
                return [
                    'student_id' => $s->id,
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