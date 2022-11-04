<?php

namespace App\Models;

use App\Enums\MediaType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $unique_key
 * @property int $rating
 * @property int $title
 * @property MediaType $type
 * @property string $disk
 * @property string $path
 * @property string $source
 * @property array $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
}
