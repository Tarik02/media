<?php

use App\Http\Controllers\{
    MediaController,
    RandomController,
    RatingController
};

Route::get('get/{mediaById}')->uses([MediaController::class, 'get']);

Route::post('rating/{mediaById}/like')->uses([RatingController::class, 'like']);
Route::post('rating/{mediaById}/dislike')->uses([RatingController::class, 'dislike']);

Route::match(['GET', 'POST'], 'random/{mediaType?}')->uses([RandomController::class, 'random']);
