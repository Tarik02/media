<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function get(Media $media): JsonResponse
    {
        return new JsonResponse([
            'data' => MediaResource::make($media),
        ]);
    }
}
