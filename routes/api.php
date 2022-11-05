<?php

use App\Http\Controllers\RandomController;

Route::match(['GET', 'POST'], 'random/{mediaType?}')->uses([RandomController::class, 'random']);
