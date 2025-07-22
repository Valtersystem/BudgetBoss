<?php
namespace App\Policies;
use App\Models\FinancialInstitution;
use App\Models\User;

class FinancialInstitutionPolicy
{
    public function update(User $user, FinancialInstitution $financialInstitution): bool
    {
        return $user->id === $financialInstitution->user_id;
    }

    public function delete(User $user, FinancialInstitution $financialInstitution): bool
    {
        return $user->id === $financialInstitution->user_id;
    }
}
