<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\VerifyStudentController;

Route::group([

    'middleware' => 'api'

], function () {
    Route::post('/admin-login', [AuthController::class, 'adminLogin'] );
    Route::post('/user-login', [AuthController::class, 'userLogin'] );
    Route::post('/verify-student', [VerifyStudentController::class, 'studentVerification'] );
});

Route::group([

    'middleware' => ['api','auth:api'],

], function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboardInfo'] );
    Route::get('/userDashboard', [UserDashboardController::class, 'userDashboardInfo'] );
    Route::post('/admin-logout', [AuthController::class, 'adminLogout'] );
    Route::post('/user-logout', [AuthController::class, 'userLogout'] );
});
