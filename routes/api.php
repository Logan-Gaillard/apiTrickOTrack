<?php

use App\Http\Controllers\MarkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;

Route::get('/test', [TestController::class, 'responseGet']);
Route::post('/test', [TestController::class, 'responsePost']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/myAccount', [UserController::class, 'getUser']);
    Route::post('/mark/createHouse', [MarkController::class, 'createHouse']);
    Route::post('/mark/createEvent', [MarkController::class, 'createEvent']);

    Route::post('/mark/marks', [MarkController::class, 'getMarksArround']);
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
