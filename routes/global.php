<?php

use App\Http\Controllers\DownloadController;

Route::get('download/{fileById}/{filename}')->uses([DownloadController::class, 'download']);
