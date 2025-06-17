<?php

use App\Http\Controllers\API\AuthApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\API\ConsignmentApiController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;


// Public routes
// Route::post('/login', [AuthApiController::class, 'login']);
// Route::post('/register', [AuthApiController::class, 'register']);

Route::middleware('guest')->group(function () {
    Route::prefix('login')->group(function () {
        Route::post('/', [AuthApiController::class, 'login']);
        Route::post('/register', [AuthApiController::class, 'register']);
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
        Route::post('reset-password', [NewPasswordController::class, 'store']);
    });
});

// Public consignment routes
// Route::get('/consignments', [ConsignmentApiController::class, 'index']);
// Route::get('/consignments/{consignment_id}', [ConsignmentApiController::class, 'show']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout',[AuthApiController::class, 'logout']);

    // CONSIGNMENT routes
    Route::prefix('consignment')->group(function () {
        Route::get('/', [ConsignmentController::class, 'laporanIndex']);
        Route::post('/', [ConsignmentApiController::class, 'store']);
        Route::get('/edit/{consignment_id}', [ConsignmentController::class, 'laporanEdit']);
        Route::get('/{consignment_id}', [ConsignmentApiController::class, 'show']);
        Route::put('/update/{consignment_id}', [ConsignmentApiController::class, 'update']);
        Route::delete('/delete/{consignment_id}', [ConsignmentApiController::class, 'destroy']);
        Route::get('/print/{consignment_id}', [ConsignmentController::class, 'printReceipt']);
    });

    // DASHBOARD ROUTES    
    Route::prefix('dashboard')->group(function () {
        
        Route::get('/', [ConsignmentController::class, 'mainpageIndex']);
        Route::get('/search', [ConsignmentApiController::class, 'mainpageSearch']);

        Route::get('/income-percentage/7', [KeuanganController::class, 'getIncomePercentageLast7Days']);
        Route::get('/income-percentage/14', [KeuanganController::class, 'getIncomePercentageLast14Days']);
        Route::get('/income-percentage/30', [KeuanganController::class, 'getIncomePercentageLast30Days']);
        Route::get('/income-percentage/365', [KeuanganController::class, 'getIncomePercentageLast12Months']);

        Route::get('/daily-report', [KeuanganController::class, 'getDailyReport']);
        Route::get('/fortnightly-report', [KeuanganController::class, 'getFortnightlyReport']);
        Route::get('/weekly-report', [KeuanganController::class, 'getWeeklyReport']);
        Route::get('/monthly-report', [KeuanganController::class, 'getMonthlyReport']);
        Route::get('/store-income-percentage', [KeuanganController::class, 'storeIncomes']);
    });


    Route::prefix('expense')->group(function () {
        Route::get('/',[ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store'])->name('barang.store');
        Route::get('/edit/{expense_id}', [ExpenseController::class, 'edit']);
        Route::put('/update/{expense_id}', [ExpenseController::class, 'update']);
        Route::delete('/delete/{expense_id}', [ExpenseController::class, 'destroy']);
        Route::post('/pdf', [ExpenseController::class, 'download']);
    });

    Route::middleware('owner')->group(function () {
        // route ke manajemen pegawai, search, dan CRUD pegawai
        Route::prefix('pegawai')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::delete('/delete/{user_id}', [UserController::class, 'destroy']);
            Route::get('/edit/{user_id}', [UserController::class, 'edit']);
            Route::put('/update/{user_id}', [UserController::class, 'update']);
            Route::get('/search', [UserController::class, 'search']);
        });
    });
});