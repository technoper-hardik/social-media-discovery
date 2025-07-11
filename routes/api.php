<?php

use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Route;

Route::post('discover', [DiscoveryController::class, 'create']);

Route::get('discover/{company}', [DiscoveryController::class, 'show']);
