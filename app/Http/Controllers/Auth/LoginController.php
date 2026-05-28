<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $phone      = $request->input('phone');
        $remember   = $request->boolean('remember');
        $normalised = $this->normalisePhone($phone);

        $user = User::where('phone', $phone)->orWhere('phone', $normalised)->first();

        if ($user && Auth::attempt(['phone' => $user->phone, 'password' => $request->input('password')], $remember)) {
            if (!$user->is_active) {
                Auth::logout();
                return back()->withInput($request->only('phone'))
                    ->withErrors(['phone' => 'Your account has been deactivated. Contact your administrator.']);
            }
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('phone'))
            ->withErrors(['phone' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '234') && strlen($digits) === 13) return $digits;
        if (str_starts_with($digits, '0') && strlen($digits) === 11) return '234' . substr($digits, 1);
        if (strlen($digits) === 10) return '234' . $digits;
        return $digits;
    }
}
