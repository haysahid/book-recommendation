<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            return redirect()->intended('/admin/scraping');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
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
