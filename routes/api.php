<?php

use App\Http\Controllers\AlertController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;

Route::get('/test', [TestController::class, 'responseGet']);
Route::post('/test', [TestController::class, 'responsePost']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/myAccount', [UserController::class, 'getUser']);
    Route::post('/alert/createHouse', [AlertController::class, 'createHouse']);
    Route::post('/alert/createEvent', [AlertController::class, 'createEvent']);
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
