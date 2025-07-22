<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Settings\AccountTypeController;
use App\Http\Controllers\Settings\CategoryController;
use App\Http\Controllers\Settings\FinancialInstitutionController;
use App\Http\Controllers\Settings\TagController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('settings/categories', CategoryController::class);
Route::resource('settings/tags', TagController::class);
Route::resource('settings/financial-institutions', FinancialInstitutionController::class);
Route::resource('settings/account-types', AccountTypeController::class);

Route::resource('accounts', AccountController::class)->except(['show']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
