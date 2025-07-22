<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\FinancialInstitution;
use App\Models\Tag;
use App\Policies\AccountPolicy;
use App\Policies\AccountTypePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\FinancialInstitutionPolicy;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Tag::class => TagPolicy::class,
        Account::class => AccountPolicy::class,
        FinancialInstitution::class => FinancialInstitutionPolicy::class,
        AccountType::class => AccountTypePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
