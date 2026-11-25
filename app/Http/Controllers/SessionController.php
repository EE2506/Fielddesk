<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Login');
    }

    public function registration(): Response
    {
        return Inertia::render('Register');
    }

    public function register(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower($request->input('email'))]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:254|unique:users,email',
            'role' => ['required', Rule::in(['agent', 'client'])],
            'password' => ['required', 'string', 'confirmed', Password::min(12), 'max:72', function ($attribute, $value, $fail) {
                if (is_string($value) && strlen($value) > 72) {
                    $fail('Use a password of 72 bytes or fewer.');
                }
            }],
        ]);

        $user = new User($data);
        $user->role = $data['role'];

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'An account already uses this email.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return to_route($user->homeRoute());
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email:rfc|max:254',
            'password' => 'required|string|max:72',
        ]);
        $credentials['email'] = strtolower($credentials['email']);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }

        $request->session()->regenerate();
        $home = route($request->user()->homeRoute());
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && ($intended === $home || str_starts_with($intended, $home.'/') || str_starts_with($intended, $home.'?'))) {
            return redirect()->to($intended);
        }

        return redirect()->to($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
