<?php

use App\Http\Controllers\Api\EnvironmentFileController;
use Illuminate\Support\Facades\Route;

Route::get('/env/{projectIdentifier}', EnvironmentFileController::class)
    ->middleware('throttle:env-file')
    ->where(['projectIdentifier' => '[A-Za-z0-9\-]+'])
    ->name('api.env.show');
