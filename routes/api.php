<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;


Route::middleware(['auth:sanctum'])->group(function () {

    
    Route::get('/messages/{conversation}', [MessageController::class, 'index'])->name('messages.index');

    
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

});