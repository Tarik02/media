<?php

use App\Http\Controllers\DownloadController;

Route::get('download/{fileById}')->uses([DownloadController::class, 'download']);
