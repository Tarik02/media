<?php

use App\Http\Controllers\RandomController;

Route::get('get/{mediaById}')->uses([MediaController::class, 'get']);

Route::match(['GET', 'POST'], 'random/{mediaType?}')->uses([RandomController::class, 'random']);
