<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Arr;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\{
    JsonResponse,
    Request,
    Response
};

class RandomController extends Controller
{
    public function random(Request $request, ?MediaType $mediaType = null): JsonResponse
    {
        $from = $request->filled('from')
            ? \transform(
                $request->input('from'),
                fn ($from) => \is_string($from)
                    ? \explode(',', $from)
                    : Arr::wrap($from)
            )
            : null;

        $query = Media::query();

        if ($mediaType !== null) {
            $query->where('type', '=', $mediaType);
        }

        if ($from !== null) {
            $query->where(function (Builder $q) use ($from) {
                foreach ($from as $item) {
                    $q->orWhere('unique_key', 'LIKE', '/' . $item . '%');
                }
            });
        }

        $totalCount = (clone $query)->count();
        if ($totalCount === 0) {
            return new JsonResponse(
                [
                    'data' => null,
                ],
                Response::HTTP_I_AM_A_TEAPOT
            );
        }

        /** @var Media|null */
        $media = (clone $query)
            ->offset(
                \mt_rand(0, $totalCount - 1)
            )
            ->first();

        if ($media === null) {
            return new JsonResponse(
                [
                    'data' => null,
                ],
                Response::HTTP_I_AM_A_TEAPOT
            );
        }

        return new JsonResponse([
            'data' => MediaResource::make($media),
        ]);
    }
}
