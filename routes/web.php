<?php

use App\Http\Controllers\ProfileController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use App\Traits\HasMailboxConnection;
use Illuminate\Support\Facades\Cache;
Route::get('/', function () {
    return redirect('/dashboard');
    //view('welcome');
});

Route::get('/dashboard', function () {



    $settings = Setting::all(['key', 'value'])->mapWithKeys(function ($item) {
        return [$item['key'] => $item['value']];
    });

    return view('dashboard', compact('settings'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
