<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    // ── List pages ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $role   = $request->input('role', 'all');
        $search = $request->input('search', '');

        $users = User::query()
            ->when($role !== 'all', fn($q) => $q->where('role', $role))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->where('id', '!=', Auth::id())
            ->orderBy('role')->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'all'                => User::where('id', '!=', Auth::id())->count(),
            'general_manager'    => User::where('role', 'general_manager')->count(),
            'production_manager' => User::where('role', 'production_manager')->count(),
            'store_manager'      => User::where('role', 'store_manager')->count(),
            'distributor'        => User::where('role', 'distributor')->count(),
        ];

        return view('users.index', compact('users', 'counts', 'role', 'search'))
            ->with('user', Auth::user());
    }

    // ── Create form ────────────────────────────────────────────────

    public function create(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $forRole = $request->input('role', 'distributor');
        return view('users.create', compact('forRole'))->with('user', Auth::user());
    }

    // ── Store new user ─────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'email'        => ['nullable', 'email', 'unique:users,email'],
            'phone'        => ['required', 'string', Rule::unique('users', 'phone')],
            'role'         => ['required', Rule::in(['general_manager', 'production_manager', 'store_manager', 'distributor'])],
            'company_name' => ['nullable', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'max:60'],
            'address'      => ['nullable', 'string'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // $data['phone']    = $this->normalisePhone($data['phone']);
        $data['phone']    = $data['phone'];
        $data['password'] = Hash::make($data['password']);
        $data['is_active']= true;

        $newUser = User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', "{$newUser->role_label} '{$newUser->name}' created successfully.");
    }

    // ── Edit form ──────────────────────────────────────────────────

    public function edit(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        return view('users.edit', compact('user'))->with('user', $user)->with('authUser', Auth::user());
    }

    // ── Update user ────────────────────────────────────────────────

    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'email'        => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'        => ['required', 'string', Rule::unique('users', 'phone')->ignore($user->id)],
            'company_name' => ['nullable', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'max:60'],
            'address'      => ['nullable', 'string'],
            'is_active'    => ['nullable', 'boolean'],
            'password'     => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data['phone']     = $this->normalisePhone($data['phone']);
        $data['is_active'] = $request->boolean('is_active', true);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "'{$user->name}' updated successfully.");
    }

    // ── Toggle active status ───────────────────────────────────────

    public function toggleActive(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "'{$user->name}' {$status}.");
    }

    // ── Delete user ────────────────────────────────────────────────

    public function destroy(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        abort_if($user->id === Auth::id(), 403, 'You cannot delete your own account.');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' deleted successfully.");
    }

    // ── Impersonate user ───────────────────────────────────────────

    public function impersonate(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        abort_if($user->id === Auth::id(), 400, 'You cannot impersonate yourself.');

        session(['impersonator_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Now impersonating '{$user->name}'.");
    }

    // ── Stop Impersonating ─────────────────────────────────────────

    public function stopImpersonate()
    {
        $originalId = session('impersonator_id');
        if (!$originalId) {
            return redirect()->route('dashboard');
        }

        $originalUser = User::findOrFail($originalId);

        session()->forget('impersonator_id');
        Auth::login($originalUser);

        return redirect()->route('admin.users.index')
            ->with('success', "Welcome back, {$originalUser->name}.");
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '234') && strlen($digits) === 13) return $digits;
        if (str_starts_with($digits, '0') && strlen($digits) === 11) return '234' . substr($digits, 1);
        if (strlen($digits) === 10) return '234' . $digits;
        return $digits;
    }
}
