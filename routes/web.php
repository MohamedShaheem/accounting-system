<?php

use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\CashBorrowLendController;
use App\Http\Controllers\DailyExpenseController;
use App\Http\Controllers\DaybookController;
use App\Http\Controllers\IndividualAccountController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\SalesbookController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\UserController;
use App\Models\SalesBook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Route::middleware(['auth','role:superadmin'])->group(function(){
    Route::get('/superadmin/dashboard', [SuperadminController::class, 'index'])->name('superadmin.index');

    //sync routes
    Route::get('/sync', [SyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/process', [SyncController::class, 'syncOrders'])->name('sync.process') ->middleware( 'throttle:pos-sync');

    Route::resource('daybook', DaybookController::class);
    Route::resource('salesbook', SalesbookController::class);
    Route::resource('bank-transaction', BankTransactionController::class);

    Route::get('/daily-expense-code', [DailyExpenseController::class, 'daily_expense_code'])
    ->name('daily-expense-code.index');

    Route::post('/daily-expense-code', [DailyExpenseController::class, 'store_daily_expense_code'])
        ->name('daily-expense-code.store');

    Route::put('/daily-expense-code/{id}', [DailyExpenseController::class, 'update_daily_expense_code'])
        ->name('daily-expense-code.update');

    Route::resource('daily-expense', DailyExpenseController::class);

     // Individual Account Management Routes (AJAX)
    Route::post('/individual-account-store', [IndividualAccountController::class, 'storeAccount'])
        ->name('individual-account-store');
    Route::get('/individual-account-get/{id}', [IndividualAccountController::class, 'getAccount'])
        ->name('individual-account-get');
    Route::put('/individual-account-update/{id}', [IndividualAccountController::class, 'updateAccount'])
        ->name('individual-account-update');
    Route::delete('/individual-account-delete/{id}', [IndividualAccountController::class, 'destroyAccount'])
        ->name('individual-account-delete');

    // Individual Account Routes
    Route::get('/individual-account-overview', [IndividualAccountController::class, 'overview'])
        ->name('individual-account.overview');
    Route::resource('individual-account', IndividualAccountController::class);


    // Cash Borrow Lend Account Management Routes (AJAX)
    Route::post('/cash-borrow-lend-store', [CashBorrowLendController::class, 'storeAccount'])
        ->name('cash-borrow-lend-store');
    Route::get('/cash-borrow-lend-get/{id}', [CashBorrowLendController::class, 'getAccount'])
        ->name('cash-borrow-lend-get');
    Route::put('/cash-borrow-lend-update/{id}', [CashBorrowLendController::class, 'updateAccount'])
        ->name('cash-borrow-lend-update');
    Route::delete('/cash-borrow-lend-delete/{id}', [CashBorrowLendController::class, 'destroyAccount'])
        ->name('cash-borrow-lend-delete');

    // Cash Borrow Lend Routes
    Route::get('/cash-borrow-lend-overview', [CashBorrowLendController::class, 'overview'])
        ->name('cash-borrow-lend.overview');
    Route::resource('cash-borrow-lend', CashBorrowLendController::class);

    Route::resource('users', UserController::class);

     // Login History Routes
    Route::get('/login-history', [LoginHistoryController::class, 'index'])
        ->name('login-history.index');
    Route::get('/login-history/user/{userId}', [LoginHistoryController::class, 'userHistory'])
        ->name('login-history.user');
    Route::delete('/login-history/{id}', [LoginHistoryController::class, 'destroy'])
        ->name('login-history.destroy');
    Route::post('/login-history/clear-old', [LoginHistoryController::class, 'clearOldRecords'])
        ->name('login-history.clear-old');

});



// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
