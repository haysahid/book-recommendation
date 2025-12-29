<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        Log::info('Fetching user list', [
            'limit' => $limit,
            'search' => $search,
            'order_by' => $orderBy,
            'order_direction' => $orderDirection,
        ]);

        $users = UserRepository::getUsers(
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        Log::info('Accessing user creation page');
        $roles = RoleRepository::getRoleDropdown();
        return Inertia::render('Admin/User/AddUser', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Storing new user', ['request_data' => $request->all()]);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        UserRepository::createUser($validatedData);

        Log::info('User created successfully', ['user' => $validatedData]);
        return redirect()->route('admin.user.index')->with('success', 'User successfully created.');
    }

    public function show(User $user)
    {
        Log::info('Fetching user details', ['user_id' => $user->id]);
        $user = UserRepository::getUserDetail($user->id);
        $userStats = UserRepository::getUserStats($user->id);
        return Inertia::render('Admin/User/UserDetail', [
            'user' => $user,
            'stats' => $userStats,
        ]);
    }

    public function edit(User $user)
    {
        Log::info('Accessing user edit page', ['user_id' => $user->id]);
        $roles = RoleRepository::getRoleDropdown();
        $user = UserRepository::getUserDetail($user->id);
        return Inertia::render('Admin/User/EditUser', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, User $user)
    {
        Log::info('Updating user', ['user_id' => $user->id, 'request_data' => $request->all()]);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        UserRepository::updateUser(
            user: $user,
            data: $validatedData,
        );

        Log::info('User updated successfully', ['user_id' => $user->id]);
        return redirect()->route('admin.user.index')->with('success', 'User successfully updated.');
    }

    public function destroy(User $user)
    {
        Log::info('Deleting user', ['user_id' => $user->id]);
        UserRepository::deleteUser($user);
        Log::info('User deleted successfully', ['user_id' => $user->id]);
        return redirect()->route('admin.user.index')->with('success', 'User successfully deleted.');
    }
}
