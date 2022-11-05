<?php

use App\Enums\MediaType;

use App\Models\{
    File,
    Media
};

Route::bind(
    'mediaType',
    fn (string $type) => MediaType::tryFrom($type) ?? abort(404)
);

Route::model('mediaById', Media::class);

Route::model('fileById', File::class);
