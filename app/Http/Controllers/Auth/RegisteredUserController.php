<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('login')->with('status', 'Registrasi mandiri dinonaktifkan. Hubungi super admin.');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        throw ValidationException::withMessages([
            'register' => 'Registrasi mandiri dinonaktifkan. Pembuatan user dilakukan manual oleh super admin.',
        ]);
    }
}
