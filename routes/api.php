<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PlaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;

Route::get('/test', [TestController::class, 'responseGet']);
Route::post('/test', [TestController::class, 'responsePost']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/myAccount', [UserController::class, 'getUser']);
    Route::post('/mark/createHouse', [PlaceController::class, 'createHouse']);
    Route::post('/mark/createEvent', [PlaceController::class, 'createEvent']);

    Route::post('/places/get', [PlaceController::class, 'getPlaces']);

    Route::post('/sweeter/sharepos', [UserController::class, 'updateUserPosition']);
    Route::post('/sweeter/getnearby', [UserController::class, 'getSweeterNearby']);

    Route::post('/contact/getMessages', [ContactController::class, 'getMessages']);
    Route::post('/contact/get', [ContactController::class, 'getOrCreate']);
    Route::post('/contact/sendmsg', [ContactController::class, 'sendMessage']);
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
