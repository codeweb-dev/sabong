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
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

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

// Route::get('/print-test', function () {
//     try {
//         // Use your printer name (the one you renamed)
//         $connector = new WindowsPrintConnector("POS-80");

//         $printer = new Printer($connector);

//         // Header
//         $printer->setJustification(Printer::JUSTIFY_CENTER);
//         $printer->setTextSize(2, 2);
//         $printer->text("Sabong\n");
//         $printer->setTextSize(1, 1);
//         $printer->feed();

//         // Divider
//         $printer->text("--------------------------------------------\n");

//         // Receipt details
//         $printer->setJustification(Printer::JUSTIFY_LEFT);
//         $printer->text("Receipt No: 001\n");
//         $printer->text("Date: " . date('Y-m-d H:i:s') . "\n");
//         $printer->text("------------------------------------------------\n");
//         $printer->text("Bet Panalo       PHP 100.00\n");
//         $printer->text("------------------------------------------------\n");
//         $printer->setEmphasis(true);
//         $printer->text("TOTAL:       PHP 100.00\n");
//         $printer->setEmphasis(false);
//         $printer->feed();

//         // Barcode section
//         $printer->setJustification(Printer::JUSTIFY_CENTER);
//         $printer->text("Scan for verification\n");
//         $printer->barcode("00123456", Printer::BARCODE_CODE39);
//         $printer->feed(2);

//         // Footer
//         $printer->text("Thank you for betting!\n");
//         $printer->feed(3);

//         // Cut and close
//         $printer->cut();
//         $printer->close();

//         return "✅ Print job with barcode sent to POS-80!";
//     } catch (Exception $e) {
//         return "❌ Print failed: " . $e->getMessage();
//     }
// });

require __DIR__.'/auth.php';
