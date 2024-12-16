<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VoteController;


Route::middleware('api')->post('v1/login', [AuthController::class, 'login']);


// Route::post('v1/admin/register', [AuthController::class, 'register']);

Route::get('v1/candidates', [AdminController::class, 'getCandidates']);
// Group untuk admin, hanya bisa diakses admin
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {
    Route::post('candidates', [AdminController::class, 'addCandidate']);
    Route::put('candidates/{id}', [AdminController::class, 'updateCandidate']);
    Route::delete('candidates/{id}', [AdminController::class, 'deleteCandidate']);
    Route::get('vote/results', [VoteController::class, 'resultVote']);
    Route::post('register', [AuthController::class, 'register']);
});

// Group untuk voting, bisa diakses oleh semua user yang sudah login
Route::middleware('auth:sanctum')->prefix('v1/vote')->group(function () {
    // Voting untuk kandidat
    Route::post('', [VoteController::class, 'vote']);
});

Route::middleware('auth:sanctum')->get('/v1/user', function (Request $request) {
    return $request->user();
});
