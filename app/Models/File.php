<?php

namespace App\Models;

use App\Enums\FileType;
use App\Http\Controllers\DownloadController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * @property int $id
 * @property FileType $type
 * @property array $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class File extends Model
{
    public $guarded = [
        'id',
    ];

    public $casts = [
        'type' => FileType::class,
        'data' => 'array',
    ];

    public static function fromStorage(string $disk, string $path): self
    {
        $file = new self;
        $file->type = FileType::STORAGE;
        $file->data = [
            'disk' => $disk,
            'path' => $path,
        ];
        $file->save();
        return $file;
    }

    public static function fromPublicUrl(string $url): self
    {
        $file = new self;
        $file->type = FileType::PUBLIC;
        $file->data = [
            'url' => $url,
        ];
        $file->save();
        return $file;
    }

    public static function fromYtdlpUrl(string $url): self
    {
        $file = new self;
        $file->type = FileType::YTDLP;
        $file->data = [
            'url' => $url,
        ];
        $file->save();
        return $file;
    }

    public function publicUrl(): string
    {
        return match ($this->type) {
            FileType::STORAGE => Storage::disk($this->data['disk'])->url($this->data['path']),
            FileType::PUBLIC, FileType::YTDLP => \action([DownloadController::class, 'download'], [$this]),
        };
    }
}
