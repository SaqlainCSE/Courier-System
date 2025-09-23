<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable', 'in:customer,courier,admin'], // optional, default customer
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'business_name' => $request->business_name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'role' => $request->role ?? 'customer', // default customer
            'password' => Hash::make($request->password),
        ]);

        // Fire registered event
        event(new Registered($user));

        // Login user
        Auth::login($user);

        // Redirect role-wise
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.shipments.index');
            case 'courier':
                return redirect()->route('courier.dashboard');
            case 'customer':
            default:
                return redirect()->route('shipments.dashboard');
        }
    }
}
