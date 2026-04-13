<?php

namespace App\Http\Controllers\Tools;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $users = User::query()
            ->select(['id', 'unit_id', 'employee_id', 'name', 'email', 'role', 'job_title', 'phone', 'is_active', 'created_at'])
            ->with('unit:id,name')
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->simplePaginate(15)
            ->withQueryString();

        return view('tools.users.index', [
            'users' => $users,
            'units' => Unit::query()->orderBy('name')->pluck('name', 'id'),
            'roles' => collect(UserRole::cases())->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create($validated + ['is_active' => (bool) ($validated['is_active'] ?? true)]);

        return back()->with('status', 'User baru berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUsers(), 403);

        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated + ['is_active' => (bool) ($validated['is_active'] ?? false)]);

        return back()->with('status', 'Data user diperbarui.');
    }
}
