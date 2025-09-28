<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaItemPageController;

Route::get('/', [MediaItemPageController::class, 'index'])
    ->name('media.items.page');
