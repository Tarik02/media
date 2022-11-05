<?php

namespace App\Models;

use App\Enums\MediaType;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\{
    Relations\BelongsTo,
    Model
};

/**
 * @property int $id
 * @property string $unique_key
 * @property int $file_id
 * @property int $rating
 * @property int $rating_positive
 * @property int $rating_negative
 * @property int $title
 * @property MediaType $type
 * @property string $source
 * @property array $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read File|RemoteFile|YtdlpFile $file
 */
class Media extends Model
{
    public $guarded = [
        'id',
    ];

    public $casts = [
        'type' => MediaType::class,
        'data' => 'array',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
