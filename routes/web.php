<?php

use App\Http\Controllers\ProfileController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use App\Traits\HasMailboxConnection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Traits\Helpers;
use Google\Service\CloudSourceRepositories\Repo;
use Illuminate\Http\Request;

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


/* Experimental: scrape a web page
 visit /browse?url=https://google.com
*/
Route::get('browse', function (Request $request) {

    $url = $request->url;
    //dd($url);
    // create an abstract class
    $abs = new class {
        // use the trait
        use Helpers;
    };

    // try calling the getWebPageContent method
    try {
        $content = $abs->getWebPageContent($url);
    } catch (\Throwable $th) {
        $content = 'ERROR: The page cannot be found' . $th->getMessage();
    }

    // return the plain text version of the page
    return $abs->convertHtmlToPlainText($content);
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account management routes
    Route::get('/accounts', function () {
        return view('accounts.index');
    })->name('accounts.index');

    // Unified inbox route
    Route::get('/unified-inbox', function () {
        $settings = Setting::all(['key', 'value'])->mapWithKeys(function ($item) {
            return [$item['key'] => $item['value']];
        });

        return view('inbox.unified', compact('settings'));
    })->name('inbox.unified');
});

require __DIR__ . '/auth.php';
