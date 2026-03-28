<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ApplicantController;
use App\Http\Controllers\API\ScholarshipController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

Route::get('/scholarships',      [ScholarshipController::class, 'index']);
Route::get('/scholarships/{id}', [ScholarshipController::class, 'show']);

// Protected Routes (need to be logged in)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Users (Admin CRUD)
    Route::get('/users',         [UserController::class, 'index']);
    Route::post('/users',        [UserController::class, 'store']);
    Route::get('/users/{id}',    [UserController::class, 'show']);
    Route::put('/users/{id}',    [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Applicants
    Route::get('/applicants',         [ApplicantController::class, 'index']);
    Route::post('/applicants',        [ApplicantController::class, 'store']);
    Route::get('/applicants/{id}',    [ApplicantController::class, 'show']);
    Route::put('/applicants/{id}',    [ApplicantController::class, 'update']);
    Route::delete('/applicants/{id}', [ApplicantController::class, 'destroy']);

    // Scholarships
    Route::post('/scholarships',        [ScholarshipController::class, 'store']);
    Route::put('/scholarships/{id}',    [ScholarshipController::class, 'update']);
    Route::delete('/scholarships/{id}', [ScholarshipController::class, 'destroy']);

    // Applications
    Route::post('/applications/submit',         [ApplicationController::class, 'submit']);
    Route::get('/applications/my-applications', [ApplicationController::class, 'myApplications']);
    Route::get('/applications',                 [ApplicationController::class, 'index']);
    Route::put('/applications/{id}/approve',    [ApplicationController::class, 'approve']);
    Route::put('/applications/{id}/reject',     [ApplicationController::class, 'reject']);
    Route::put('/applications/{id}',            [ApplicationController::class, 'update']);  // ADD THIS
});