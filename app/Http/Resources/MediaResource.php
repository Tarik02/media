<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Media $resource
 */
class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'type' => $this->resource->type->value,
            'rating' => [
                'total' => $this->resource->rating,
                'likes' => $this->resource->rating_positive,
                'dislikes' => $this->resource->rating_negative,
            ],
            'source' => $this->resource->source,
            'url' => $this->resource->file->publicUrl(),
        ];
    }
}
