<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\User;
use DB;
use Illuminate\Database\QueryException;

use Illuminate\Support\Str;

class AdminService
{
    public function __construct(protected MailService $mailService)
    {
    }

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



    public function listAdmin(array $params): mixed
    {
        $query = $this->excludeRolesQuery();

        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$params['search']}%"])
                    ->orWhere('email', 'like', '%' . $params['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $params['search'] . '%');
            });
        }

        if (isset($params['status']) && $params['status'] !== 'all') {
            $query->where('status', $params['status']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $allowedSortFields = ['id', 'name', 'email', 'phone', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $limit = $params['per_page'] ?? 10;

        return $query->select([
            'id',
            'name',
            'email',
            'phone',
            'avatar',
            'status',
            'created_at'
        ])->paginate($limit);
    }

    public function createAdmin(array $data): array
    {
        $password = Str::random(10);
        try {
            $user = DB::transaction(function () use ($data, $password) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                    'status' => $data['status'],
                    'password' => $password,
                ]);

                $user->syncRoles($data['roles']);

                return $user;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23505' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == '1062')) {
                throw new UserException('Email đã tồn tại');
            }
            throw $e;
        }

        $this->mailService->sendAdminAccountCreatedInfo($user, $password);

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

    public function getProfile(): array
    {
        return $this->getAdmin(auth()->id());
    }

    public function updateProfile(array $data): array
    {
        $user = $this->excludeRolesQuery()->where('id', auth()->id())->first();

        if (!$user) {
            throw new UserException('Không tìm thấy thông tin quản trị viên');
        }

        try {
            DB::transaction(function () use ($user, $data) {
                $user->update(array_filter([
                    'name' => $data['name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                ], fn($v) => !is_null($v)));
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $this->formatAdmin($user->fresh('roles'));
    }

    private function excludeRolesQuery()
    {
        return User::with('roles')->doesntHave('student')->doesntHave('teacher')->doesntHave('guardian');
    }
}
