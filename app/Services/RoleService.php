<?php

namespace App\Services;
use App\Exceptions\UserException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{

    public static function listRole()
    {
        return Role::query()
            ->whereNotIn('name', ['student', 'teacher', 'guardian'])
            ->get(['id', 'name']);
    }

    public static function createRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);
        $role = Role::create($data);
        return [
            'id' => $role->id,
            'name' => $role->name
        ];
    }

    public static function updateRole(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $role = Role::findOrFail($id);
        $role->update([
            'name' => $data['name'],
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')
        ];
    }

    public static function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name == 'super_admin') {
            throw new UserException('Không thể xóa vai trò super_admin');
        }
        $role->delete();
        return $role->id;
    }

    public static function listPermission()
    {
        $roles = Role::query()
            ->whereNotIn('name', ['student', 'teacher', 'guardian'])
            ->with('permissions:id,name')
            ->get(['id', 'name'])
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')
                ];
            });

        $allPermissions = Permission::query()->pluck('name');

        return [
            'roles' => $roles,
            'all_permissions' => $allPermissions
        ];
    }
}