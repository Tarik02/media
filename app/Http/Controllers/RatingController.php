<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use DB;

use App\Http\Requests\RatingController\{
    DislikeRequest,
    LikeRequest
};
use App\Models\{
    Media,
    MediaLike
};
use Illuminate\Http\{
    JsonResponse,
    Response
};

class RatingController extends Controller
{
    public function like(LikeRequest $request, Media $media): JsonResponse
    {
        if ($this->setLikeValue($media, $request->voter, 1)) {
            $media->refresh();

            return new JsonResponse([
                'data' => MediaResource::make($media),
            ], Response::HTTP_ACCEPTED);
        } else {
            return new JsonResponse([
                'data' => MediaResource::make($media),
            ]);
        }
    }

    public function dislike(DislikeRequest $request, Media $media): JsonResponse
    {
        if ($this->setLikeValue($media, $request->voter, -1)) {
            $media->refresh();

            return new JsonResponse([
                'data' => MediaResource::make($media),
            ], Response::HTTP_ACCEPTED);
        } else {
            return new JsonResponse([
                'data' => MediaResource::make($media),
            ]);
        }
    }

    protected function setLikeValue(Media $media, string $voter, int $value): bool
    {
        return DB::transaction(function () use ($media, $voter, $value) {
            /** @var MediaLike $like */
            $like = MediaLike::query()
                ->whereBelongsTo($media)
                ->firstOrNew([
                    'voter' => $voter,
                ]);

            $like->media()->associate($media);

            if ($like->value === $value) {
                return false;
            }

            $diff = $value - $like->value;
            $like->value = $value;
            $like->save();

            Media::query()
                ->whereKey($media->id)
                ->increment('rating', $diff);

            return true;
        });
    }
}
