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
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        $this->createDefaultWalletAccount($user);
        $this->createDefaultCategories($user);
        $this->createDefaultTags($user);

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

    protected function createDefaultTags(User $user): void
    {
        $defaultTags = [
            ['name' => 'Recurring'],
            ['name' => 'Fixed cost'],
            ['name' => 'One-time'],
            ['name' => 'Work'],
            ['name' => 'Family'],
            ['name' => 'Friends'],
            ['name' => 'Urgent'],
            ['name' => 'Investment'],
        ];

        foreach ($defaultTags as $tag) {
            $user->tags()->create($tag);
        }
    }

    protected function createDefaultCategories(User $user): void
    {
        $defaultCategories = [
            [
                'name' => 'Food',
                'icon' => 'utensils',
                'color' => '#EF4444', // Red
                'type' => 'expense',
            ],
            [
                'name' => 'Subscription',
                'icon' => 'credit-card',
                'color' => '#A855F7', // Purple
                'type' => 'expense',
            ],
            [
                'name' => 'Home',
                'icon' => 'home',
                'color' => '#0EA5E9', // Sky
                'type' => 'expense',
            ],
            [
                'name' => 'Shopping',
                'icon' => 'shopping-cart',
                'color' => '#A855F7',
                'type' => 'expense',
            ],
            [
                'name' => 'Education',
                'icon' => 'book',
                'color' => '#A855F7',
                'type' => 'expense',
            ],
            [
                'name' => 'Leisure',
                'icon' => 'gamepad-2',
                'color' => '#F97316', // Orange
                'type' => 'expense',
            ],
            [
                'name' => 'Banking',
                'icon' => 'landmark',
                'color' => '#A855F7',
                'type' => 'expense',
            ],
            [
                'name' => 'Others',
                'icon' => 'more-horizontal',
                'color' => '#6B7280', // Gray
                'type' => 'expense',
            ],
            [
                'name' => 'Pix',
                'icon' => 'dollar-sign',
                'color' => '#A855F7',
                'type' => 'income',
            ],
            [
                'name' => 'Health',
                'icon' => 'stethoscope',
                'color' => '#84CC16', // Lime
                'type' => 'expense',
            ],
            [
                'name' => 'Services',
                'icon' => 'briefcase',
                'color' => '#16A34A', // Green
                'type' => 'expense',
            ],
            [
                'name' => 'Supermarket',
                'icon' => 'shopping-cart',
                'color' => '#EF4444',
                'type' => 'expense',
            ],
            [
                'name' => 'Transport',
                'icon' => 'car',
                'color' => '#3B82F6', // Blue
                'type' => 'expense',
            ],
            [
                'name' => 'Travel',
                'icon' => 'plane',
                'color' => '#0EA5E9', // Sky
                'type' => 'expense',
            ],
        ];

        foreach ($defaultCategories as $category) {
            $user->categories()->create($category);
        }
    }
}
