<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'initial_balance',
        'color',
        'include_in_dashboard',
        'financial_institution_id',
        'account_type_id',
    ];

    protected $appends = ['current_balance', 'predicted_balance'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financialInstitution(): BelongsTo
    {
        return $this->belongsTo(FinancialInstitution::class);
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected function currentBalance(): Attribute
    {
        return Attribute::make(
            get: function () { // Removida a passagem de argumentos desnecessários
                $incomes = $this->transactions()
                    ->where('type', 'income')
                    ->where('paid', true)
                    ->sum('amount');

                $expenses = $this->transactions()
                    ->where('type', 'expense')
                    ->where('paid', true)
                    ->sum('amount');

                return $this->initial_balance + $incomes - $expenses;
            },
        );
    }
    protected function predictedBalance(): Attribute
    {
        return Attribute::make(
            get: function () {
                $incomes = $this->transactions()
                    ->where('type', 'income')
                    ->sum('amount');

                $expenses = $this->transactions()
                    ->where('type', 'expense')
                    ->sum('amount');

                // CORREÇÃO: Usar $this->initial_balance
                return $this->initial_balance + $incomes - $expenses;
            },
        );
    }
}
