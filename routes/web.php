<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankInstitutionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rota para a página inicial
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Agrupa as rotas que requerem autenticação e verificação de e-mail
Route::middleware(['auth', 'verified'])->group(function () {
    // Rota para o dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rotas de recursos para os controllers da aplicação
    Route::resource('accounts', AccountController::class);
    Route::resource('bank-institutions', BankInstitutionController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('credit-cards', CreditCardController::class);
    Route::resource('tags', TagController::class);
    Route::resource('transactions', TransactionController::class);
});

// Inclui os ficheiros de rotas de autenticação e configurações
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
