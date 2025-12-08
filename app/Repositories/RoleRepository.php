<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;

class RoleRepository
{
    public static function getRoles(
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = Role::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function getRoleDropdown($hideRoles = [])
    {
        $query = Role::query();

        if (!empty($hideRoles)) {
            $query->whereNotIn('slug', $hideRoles);
        }

        return $query->orderBy('id', 'asc')->get();
    }
}
