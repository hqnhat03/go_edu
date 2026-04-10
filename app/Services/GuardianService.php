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

    public function createGuardian(CreateRequest $request)
    {
        $data = $request->validated();
        return DB::transaction(function () use ($data) {
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
            $user->guardian()->create();
            $user->guardian->students()->sync($data['student_ids']);

            return [
                'id' => $user->guardian->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'students' => $user->guardian->students->map(function ($s) {
                    return [
                        'student_id' => $s->id,
                        'name' => $s->user->name,
                    ];
                })->values()
            ];
        });
    }

    public function listGuardian(Request $request)
    {
        $data = User::whereHas('guardian')->get()->map(function ($user) {
            return [
                'id' => $user->guardian->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $user->status,
                'students' => $user->guardian->students->map(function ($s) {
                    return [
                        'student_id' => $s->id,
                        'name' => $s->user->name,
                    ];
                })->values()
            ];
        });
        return $data;
    }

    public function updateGuardian(UpdateRequest $request, $id)
    {
        $data = $request->validated();
        $guardian = Guardian::findOrFail($id);
        return DB::transaction(function () use ($guardian, $data) {
            // Cập nhật bảng users
            try {
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
            } catch (QueryException $e) {
                if ($e->getCode() == 23000) {
                    throw new UserException("Email already exists");
                }
            }

            $guardian->students()->sync($data['student_ids']);

            return [
                'id' => $guardian->id,
                'name' => $guardian->user->name,
                'email' => $guardian->user->email,
                'avatar' => $guardian->user->avatar,
                'status' => $guardian->user->status,
                'gender' => $guardian->user->gender,
                'phone' => $guardian->user->phone,
                'address' => $guardian->user->address,
                'date_of_birth' => $guardian->user->date_of_birth,
                'students' => $guardian->students->map(function ($s) {
                    return [
                        'student_id' => $s->id,
                        'name' => $s->user->name,
                    ];
                })->values()
            ];
        });
    }

    public function getGuardian($id)
    {
        $guardian = Guardian::findOrFail($id);
        return [
            'id' => $guardian->id,
            'name' => $guardian->user->name,
            'email' => $guardian->user->email,
            'avatar' => $guardian->user->avatar,
            'status' => $guardian->user->status,
            'gender' => $guardian->user->gender,
            'phone' => $guardian->user->phone,
            'address' => $guardian->user->address,
            'date_of_birth' => $guardian->user->date_of_birth,
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
        $guardian = Guardian::findOrFail($id);
        $guardian->delete();
        return $guardian->id;
    }

}