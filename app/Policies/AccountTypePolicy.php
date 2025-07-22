<?php
namespace App\Policies;
use App\Models\AccountType;
use App\Models\User;

class AccountTypePolicy
{
    public function update(User $user, AccountType $accountType): bool
    {
        return $user->id === $accountType->user_id;
    }

    public function delete(User $user, AccountType $accountType): bool
    {
        return $user->id === $accountType->user_id;
    }
}
