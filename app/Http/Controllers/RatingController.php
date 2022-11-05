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

            $diffPositive = -max(0, $like->value) + \max(0, $value);
            $diffNegative = min(0, $like->value) + -\min(0, $value);

            $diff = $value - $like->value;
            $like->value = $value;
            $like->save();

            $query = Media::query()->whereKey($media->id);

            $query->update([
                'rating' => DB::raw(\sprintf(
                    '%s + %s',
                    $query->getGrammar()->wrap('rating'),
                    $diff,
                )),
                'rating_positive' => DB::raw(\sprintf(
                    '%s + %s',
                    $query->getGrammar()->wrap('rating_positive'),
                    $diffPositive,
                )),
                'rating_negative' => DB::raw(\sprintf(
                    '%s + %s',
                    $query->getGrammar()->wrap('rating_negative'),
                    $diffNegative,
                )),
            ]);

            return true;
        });
    }
}
