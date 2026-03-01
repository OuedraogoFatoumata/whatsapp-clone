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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ],
        [
            // Messages d'erreur en français
            'name.required'     => 'Le prénom est obligatoire.',
            'username.required' => 'Le pseudo est obligatoire.',
            'username.min'      => 'Le pseudo doit faire au moins 3 caractères.',
            'username.max'      => 'Le pseudo ne peut pas dépasser 20 caractères.',
            'username.unique'   => 'Ce pseudo est déjà pris.',
            'username.regex'    => 'Le pseudo ne peut contenir que des lettres, chiffres et underscores.',
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.unique'      => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

      

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('chat.index');
    }
}
