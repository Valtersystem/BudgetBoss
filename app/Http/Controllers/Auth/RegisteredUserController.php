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
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Lógica para criar a conta "Carteira" por defeito
        $this->createDefaultWalletAccount($user);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Create a default Wallet institution and account for a new user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function createDefaultWalletAccount(User $user): void
    {
        // Cria uma instituição "Carteira" por defeito
        $walletInstitution = $user->bankInstitutions()->create([
            'name' => 'Wallet',
            'icon' => 'wallet',
        ]);

        // Cria a conta "Carteira" associada
        $user->accounts()->create([
            'bank_institution_id' => $walletInstitution->id,
            'name' => 'Wallet',
            'initial_balance' => 0,
            'color' => '#22C55E',
            'source_of_money' => 'Money',
        ]);
    }
}
