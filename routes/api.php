<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('notes/search', [NoteController::class, 'search'])->middleware('auth:sanctum');
Route::post('user/upload', [UserController::class, 'uploadProfilePicture'])->middleware('auth:sanctum');
Route::apiResource('notes', NoteController::class)->middleware('auth:sanctum');
Route::put('user/update', [UserController::class, 'updateProfile'])->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
});
