<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
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

    Route::group(['middleware' => ['role:admin']], function () {
        //
    });

    Route::group(['middleware' => ['role:admin']], function () {
        // Route::get('/admin-noon-report', AdminNoonReport::class)->name('admin-noon-report');
    });

    Route::group(['middleware' => ['role:admin']], function () {
        //
    });
});

require __DIR__.'/auth.php';
