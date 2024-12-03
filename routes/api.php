<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EmployeeController;
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
    Route::put('/updateProfile', [AuthController::class, 'updateProfile']);

    Route::get('/logout', [AuthController::class, 'logout']);


    Route::group(['prefix' => 'company'], function () {
        Route::post('/create', [CompanyController::class, 'createCompany']);
    });

    Route::group(['prefix' => 'managers', 'middleware' => 'role:manager'], function () {
        Route::get('/', [ManagerController::class, 'index']);
        Route::get('/{id}', [ManagerController::class, 'detail']);
    });

    Route::group(['prefix' => 'employees', 'middleware' => ['role:manager', 'role:employee']], function () {
        Route::get('/', [EmployeeController::class, 'index']); // List all (including soft delete filter)
        Route::get('/{id}', [EmployeeController::class, 'show']); // Show by ID
    });
    Route::group(['prefix' => 'employees', 'middleware' => ['role:manager']], function () {
        Route::get('/', [EmployeeController::class, 'index']); // List all (including soft delete filter)
        Route::get('/{id}', [EmployeeController::class, 'show']); // Show by ID
        Route::post('/create', [EmployeeController::class, 'store']); // Create
        Route::put('/{id}', [EmployeeController::class, 'update']); // Update
        Route::delete('/{id}', [EmployeeController::class, 'destroy']); // Soft Delete
        Route::patch('/{id}/restore', [EmployeeController::class, 'restore']); // Restore
        Route::delete('/{id}/force', [EmployeeController::class, 'forceDelete']); // Force Delete
    });
});
