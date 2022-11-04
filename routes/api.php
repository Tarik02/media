<?php

use App\Enums\MediaType;
use App\Http\Controllers\RandomController;

Route::bind(
    'mediaType',
    fn (string $type) => MediaType::tryFrom($type) ?? abort(404)
);

Route::match(['GET', 'POST'], 'random/{mediaType?}')->uses([RandomController::class, 'random']);
