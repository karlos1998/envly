<?php

use App\Http\Controllers\Api\EnvironmentFileController;
use Illuminate\Support\Facades\Route;

Route::get('/env/{projectIdentifier}/{token}', EnvironmentFileController::class)
    ->middleware('throttle:env-file')
    ->where(['projectIdentifier' => '[A-Za-z0-9\-]+', 'token' => '[A-Za-z0-9_]+'])
    ->name('api.env.show');
