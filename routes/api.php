<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return auth()->user();
    });

    Route::group(['prefix' => 'company'], function () {
        Route::post('/create', [CompanyController::class, 'createCompany']);
    });

    Route::group(['prefix' => 'managers', 'middleware' => 'role:manager'], function () {
        Route::get('/', [ManagerController::class, 'index']);
        Route::get('/{id}', [ManagerController::class, 'detail']);
    });
});
