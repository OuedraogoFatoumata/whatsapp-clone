<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AiController;
 use App\Http\Controllers\StatusController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/', function () {
    return redirect()->route('chat.index');
});


    Route::middleware(['auth'])->group(function () {

   
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [ChatController::class, 'create'])->name('chat.create');

    Route::get('/messages/{conversation}', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    
Route::post('/users/statut', [UserController::class, 'updateStatut'])->name('users.statut');
    
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');

   
    Route::post('/ai/recap',       [AiController::class, 'recap'])->name('ai.recap');
    Route::post('/ai/suggest',     [AiController::class, 'suggest'])->name('ai.suggest');
    Route::post('/ai/reformulate', [AiController::class, 'reformulate'])->name('ai.reformulate');

   

Route::post('/status', [StatusController::class, 'store'])->name('status.store');
Route::get('/status', [StatusController::class, 'index'])->name('status.index');

});





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
