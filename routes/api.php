<?php

use App\Http\Controllers\P\StatusTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('addClass', [StatusTaskController::class, 'addClass']);
Route::post('addStudent', [StatusTaskController::class, 'addStudent']);
Route::post('addTask', [StatusTaskController::class, 'addTask']);
Route::post('updateTaskStatus', [StatusTaskController::class, 'updateTaskStatus']);
Route::get('getTodayStats', [StatusTaskController::class, 'getTodayStats']);
