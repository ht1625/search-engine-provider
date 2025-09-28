<?php 

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MediaItemController;

Route::get('/media-items', [MediaItemController::class, 'index']);
