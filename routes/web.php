<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankInstitutionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('accounts', AccountController::class);
    Route::resource('bank-institutions', BankInstitutionController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('credit-cards', CreditCardController::class);
    Route::resource('tags', TagController::class);
    Route::resource('transactions', TransactionController::class);

    Route::get('incomes', [TransactionController::class, 'indexIncomes'])->name('transactions.incomes.index');
    Route::get('expenses', [TransactionController::class, 'indexExpenses'])->name('transactions.expenses.index');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
