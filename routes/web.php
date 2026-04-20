<?php

use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::redirect('/dashboard', '/projects')->name('dashboard');

    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);
    Route::post('projects/{project}/environments', [EnvironmentController::class, 'store'])->name('projects.environments.store');
    Route::put('projects/{project}/environments/{environment}', [EnvironmentController::class, 'update'])->name('projects.environments.update');
    Route::post('projects/{project}/environments/{environment}/token', [EnvironmentController::class, 'regenerateToken'])->name('projects.environments.token');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
