<?php

use App\Livewire\Admin\Betting;
use App\Livewire\Admin\Transactions;
use App\Livewire\Admin\Users;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\User\Transaction;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::group(['middleware' => ['role:declarator']], function () {
        //
    });

    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/admin/users', Users::class)->name('admin.users');
        Route::get('/admin/transactions', Transactions::class)->name('admin.transactions');
        Route::get('/admin/betting', Betting::class)->name('admin.betting');
    });

    Route::group(['middleware' => ['role:user']], function () {
        Route::get('/user/transactions', Transaction::class)->name('user.transactions');
    });
});

require __DIR__.'/auth.php';
