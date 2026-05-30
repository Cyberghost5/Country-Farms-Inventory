<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => ['required', 'string']]);

        $phone      = $request->input('phone');
        // $normalised = $this->normalisePhone($phone);
        $normalised = $phone;

        $user = User::where('phone', $phone)->orWhere('phone', $normalised)->first();

        if (!$user) {
            return back()->withInput($request->only('phone'))
                ->withErrors(['phone' => 'No account found with that phone number.']);
        }

        $storedPhone = $user->phone;
        $otp         = (string) random_int(100000, 999999);
        $expires     = now()->addMinutes(10);

        DB::table('password_reset_otps')->updateOrInsert(
            ['phone' => $storedPhone],
            ['otp' => Hash::make($otp), 'expires_at' => $expires, 'created_at' => now()]
        );

        // TODO Milestone 4+: send via SMS service
        // For now, flash OTP in dev mode
        if (config('app.debug')) {
            session()->flash('dev_otp', "DEV MODE — OTP: {$otp}");
        }

        $request->session()->put('otp_phone', $storedPhone);

        return redirect()->route('password.verify')
            ->with('status', 'A 6-digit OTP has been sent to ' . $this->maskPhone($storedPhone) . '.');
    }

    public function showVerify(Request $request)
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);

        $phone = $request->session()->get('otp_phone');
        if (!$phone) {
            return redirect()->route('password.request')
                ->withErrors(['phone' => 'Session expired. Please start again.']);
        }

        $record = DB::table('password_reset_otps')->where('phone', $phone)->first();

        if (!$record || now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('phone', $phone)->delete();
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if (!Hash::check($request->input('otp'), $record->otp)) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        DB::table('password_reset_otps')->where('phone', $phone)->delete();

        $resetToken = bin2hex(random_bytes(32));
        $request->session()->put('otp_reset_token', $resetToken);
        $request->session()->put('otp_reset_phone', $phone);
        $request->session()->put('otp_reset_expires', now()->addMinutes(15)->timestamp);

        return redirect()->route('password.reset', ['token' => $resetToken]);
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '234') && strlen($digits) === 13) return $digits;
        if (str_starts_with($digits, '0') && strlen($digits) === 11) return '234' . substr($digits, 1);
        if (strlen($digits) === 10) return '234' . $digits;
        return $digits;
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4) . str_repeat('*', max(0, strlen($phone) - 7)) . substr($phone, -3);
    }
}
