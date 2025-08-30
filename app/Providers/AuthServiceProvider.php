<?php

namespace App\Providers;

use App\Models\BankInstitution;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Transaction;
use App\Policies\BankInstitutionPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TagPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        BankInstitution::class => BankInstitutionPolicy::class,
        Category::class => CategoryPolicy::class,
        Tag::class => TagPolicy::class,
        Transaction::class => TransactionPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
