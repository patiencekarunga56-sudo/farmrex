<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Only active accounts may sign in.
        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = $request->user();
            $firstTime = $user->hasNeverLoggedIn();
            $user->forceFill(['last_login_at' => now()])->saveQuietly();

            $message = $firstTime
                ? 'Welcome to FarmRex, '.$user->name.'! We are glad to have you.'
                : 'Welcome back, '.$user->name.'!';

            return redirect()->intended(route('dashboard'))->with('success', $message);
        }

        // Correct password but switched off by the administrator -> say so clearly.
        $existing = User::where('email', $credentials['email'])->first();
        if ($existing && ! $existing->is_active && Hash::check($credentials['password'], $existing->password)) {
            return back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.'])
                ->onlyInput('email');
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // The very first account becomes the administrator, everyone else is a farmer.
        $data['role'] = User::count() === 0 ? 'admin' : 'farmer';

        $user = User::create($data);

        // Do not sign them in: send them to the sign-in page to log in for the first time.
        return redirect()->route('login')
            ->withInput(['email' => $user->email])
            ->with('success', 'Account created successfully. Please sign in to continue.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
