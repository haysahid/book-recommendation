<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserRepository
{
    public static function createUser(array $data)
    {
        try {
            DB::beginTransaction();

            $data['password'] = Hash::make($data['password']);

            // Create the user
            $user = User::create($data);

            if (isset($data['profile_photo'])) {
                $path = $data['profile_photo']->store('user', 'public');
                $user->profile_photo_path = $path;
                $user->save();
            }

            if (isset($data['role'])) {
                $user->syncRoles($data['role']);
            }

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to create user: ' . $e);
            throw $e;
        }
    }

    public static function createGuestUser(array $data)
    {
        try {
            $existingGuest = User::where('email', $data['email'])
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Guest');
                })
                ->first();

            if ($existingGuest) {
                // Update existing guest user data
                $existingGuest->update($data);
                return $existingGuest;
            }

            $user = User::create($data);

            $guestRole = Role::firstOrCreate(['name' => 'Guest']);
            $user->assignRole($guestRole);

            return $user;
        } catch (Exception $e) {
            Log::error('Failed to create guest user: ' . $e);
            throw $e;
        }
    }

    public static function updateUser(User $user, array $data)
    {
        try {
            if (isset($data['password']) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if (isset($data['profile_photo'])) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $path = $data['profile_photo']->store('user', 'public');
                $data['profile_photo_path'] = $path;
            }

            $user->update($data);

            if (isset($data['role'])) {
                $user->syncRoles($data['role']);
            }

            return $user;
        } catch (Exception $e) {
            Log::error('Failed to update user: ' . $e);
            throw $e;
        }
    }

    public static function deleteUser(User $user)
    {
        try {
            $user->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete user: ' . $e);
            throw $e;
        }
    }

    public static function getUsers(
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = User::with([
            'roles',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit);

        return $users;
    }

    public static function getUserDetail($userId)
    {
        return User::with(['roles'])->findOrFail($userId);
    }

    // Get user statistics
    public static function getUserStats($userId)
    {
        $user = DB::table('users')
            ->leftJoin('transactions', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('invoices', 'transactions.id', '=', 'invoices.transaction_id')
            ->leftJoin('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('users.id', $userId)
            ->selectRaw('
                COUNT(DISTINCT invoices.id) AS total_orders,
                COALESCE(SUM(transaction_items.quantity), 0) AS books_ordered,
                COALESCE(SUM(invoices.amount), 0) AS total_spent
            ')
            ->groupBy('users.id')
            ->first();

        return [
            'total_orders' => $user->total_orders ?? 0,
            'books_ordered' => $user->books_ordered ?? 0,
            'total_spent' => $user->total_spent ?? 0,
        ];
    }
}
