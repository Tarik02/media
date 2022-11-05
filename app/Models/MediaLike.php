<?php

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\{
    Relations\BelongsTo,
    Model
};

/**
 * @property int $media_id
 * @property string $voter
 * @property int $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Media $media
 */
class MediaLike extends Model
{
    use Traits\MultipleColumnsPrimaryKey;

    protected $primaryKey = [
        'media_id',
        'voter',
    ];

    public $incrementing = false;

    public $guarded = [];

    public $attributes = [
        'value' => 0,
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
