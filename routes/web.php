<?php

use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectGithubDeploymentController;
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
    Route::delete('projects/{project}/environments/{environment}', [EnvironmentController::class, 'destroy'])->name('projects.environments.destroy');
    Route::post('projects/{project}/environments/{environment}/token', [EnvironmentController::class, 'regenerateToken'])->name('projects.environments.token');
    Route::get('projects/{project}/github', [ProjectGithubDeploymentController::class, 'edit'])->name('projects.github.edit');
    Route::get('projects/{project}/github/repositories', [ProjectGithubDeploymentController::class, 'repositories'])->name('projects.github.repositories');
    Route::get('projects/{project}/github/workflows', [ProjectGithubDeploymentController::class, 'workflows'])->name('projects.github.workflows');
    Route::put('projects/{project}/github/deployment', [ProjectGithubDeploymentController::class, 'update'])->name('projects.github.update');
    Route::post('projects/{project}/github/deploy', [ProjectGithubDeploymentController::class, 'deploy'])->name('projects.github.deploy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
