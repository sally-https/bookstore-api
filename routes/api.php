<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\VerifyStudentController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\BorrowsController;
use App\Http\Controllers\ReturnsController;

Route::group([

    'middleware' => 'api'

], function () {
    Route::post('/admin-login', [AuthController::class, 'adminLogin'] );
    Route::post('/user-login', [AuthController::class, 'userLogin'] );
    Route::post('/user-register', [AuthController::class, 'userRegister'] );
    Route::post('/verify-student', [VerifyStudentController::class, 'studentVerification'] );
});

Route::group([

    'middleware' => ['api','auth:api'],

], function () {
    Route::get('/adminDashboard', [AdminDashboardController::class, 'dashboardInfo'] );
    Route::get('/userDashboard', [UserDashboardController::class, 'userDashboardInfo'] );
    Route::get('/bookLendingDetails', [UserDashboardController::class, 'bookLendingDetails'] );
    Route::get('/userlibrary', [BooksController::class, 'userLibraryInfo'] );
    Route::post('/admin-logout', [AuthController::class, 'adminLogout'] );
    Route::post('/user-logout', [AuthController::class, 'userLogout'] );
    Route::post('/add-book',[BooksController::class, 'storeBook']);
    Route::post('/borrow-book',[BorrowsController::class, 'borrowBook']);
    Route::post('/return-book',[ReturnsController::class, 'returnBook']);
});
