<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\User;
use DB;
use Illuminate\Database\QueryException;

class AdminService
{
    private function formatAdmin(User $user): array
    {
        $data = $user->only([
            'id',
            'name',
            'email',
            'phone',
            'address',
            'gender',
            'date_of_birth',
            'avatar',
            'status',
        ]);
        $data['roles'] = $user->roles->pluck('name');

        return $data;
    }



    public function listAdmin(array $param): mixed
    {
        $query = $this->excludeRolesQuery();

        if (!empty($param['search'])) {
            $query->where(function ($q) use ($param) {
                $q->where('name', 'like', '%' . $param['search'] . '%')
                    ->orWhere('email', 'like', '%' . $param['search'] . '%');
            });
        }

        if (!empty($param['status'])) {
            $query->where('status', $param['status']);
        }

        $admins = $query->select([
            'id',
            'name',
            'email',
            'phone',
            'avatar',
            'status',
        ])->get();

        return $admins->map(
            fn($admin) => [
                ...$admin->only(['id', 'name', 'email', 'phone', 'avatar', 'status']),
                'roles' => $admin->roles->pluck('name')
            ]
        );
    }

    public function createAdmin(array $data): array
    {
        try {
            $user = DB::transaction(function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                    'status' => $data['status'],
                    'password' => $data['password'] ?? 'password',
                ]);

                $user->syncRoles($data['roles']);

                return $user;
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Email đã tồn tại');
            }
            throw $e;
        }

        return $this->formatAdmin($user);
    }

    public function getAdmin(int $id): array
    {
        $user = $this->excludeRolesQuery()->where('id', $id)->first();

        if (!$user) {
            throw new UserException('Không tìm thấy admin');
        }

        return $this->formatAdmin($user);
    }

    public function updateAdmin(array $data, int $id): array
    {
        $user = $this->excludeRolesQuery()->where('id', $id)->first();

        if (!$user) {
            throw new UserException('Không tìm thấy admin');
        }

        try {
            DB::transaction(function () use ($user, $data) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                    'status' => $data['status'],
                ]);

                $user->syncRoles($data['roles']);
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == '1062') {
                throw new UserException('Email đã tồn tại');
            }
            throw $e;
        }

        return $this->formatAdmin($user->fresh('roles') ?? []);
    }

    public function deleteAdmin(int $id): int
    {
        $user = $this->excludeRolesQuery()->where('id', $id)->first();

        if (!$user) {
            throw new UserException('Không tìm thấy admin');
        }

        $user->delete();

        return $id;
    }

    private function excludeRolesQuery()
    {
        return User::with('roles')->doesntHave('student')->doesntHave('teacher')->doesntHave('guardian');
    }
}
