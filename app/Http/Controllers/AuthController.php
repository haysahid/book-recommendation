<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login()
    {
        return Inertia::render('Auth/Login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::id());
            $accessToken = $user->createToken('authToken')->plainTextToken;

            Cookie::queue(Cookie::forever(
                name: 'access_token',
                value: $accessToken,
                secure: false,
                httpOnly: false,
            ));

            $request->session()->regenerate();

            if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
                return redirect()->intended('/admin/scraping');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        UserRepository::createUser([
            ...$validatedData,
            'role' => 'User',
        ]);

        return redirect('/login')->with('success', 'Registration successful! Please log in.');
    }

    public function logout(Request $request)
    {
        Cookie::queue(Cookie::forget('access_token'));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
