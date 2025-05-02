<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', [App\Http\Controllers\TestController::class, 'responseGet']);
Route::post('/test', [App\Http\Controllers\TestController::class, 'responsePost']);
