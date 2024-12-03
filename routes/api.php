<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return auth()->user();
    });
});

Route::group(['middleware' => 'super-admin'], function () {
    Route::get('/tes', function (Request $request) {
        return 'tes';
    });
    Route::post('/create/company', [CompanyController::class, 'createCompany']);
});
