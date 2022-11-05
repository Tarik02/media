<?php

namespace App\Models;

use App\Enums\FileType;
use App\Http\Controllers\DownloadController;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Str;
use Symfony\Component\Process\Process;

/**
 * @property int $id
 * @property FileType $type
 * @property string $filename
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
        $file->setSafeFilename($path);
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
        $file->setSafeFilename(\parse_url($url, \PHP_URL_PATH));
        $file->data = [
            'url' => $url,
        ];
        $file->save();
        return $file;
    }

    public static function fromYtdlpUrl(string $url): self
    {
        $process = new Process([
            \env('YT_DLP'),
            '--no-playlist',
            '--no-download',
            '--no-progress',
            '--dump-json',
            $url,
        ]);

        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new Exception(
                \sprintf(
                    'yt-dlp exited with non-zero exit code (%s)',
                    $process->getExitCode()
                )
            );
        }

        $data = \json_decode(
            $process->getOutput(),
            associative: true,
            flags: \JSON_THROW_ON_ERROR
        );

        $file = new self;
        $file->type = FileType::YTDLP;
        $file->setSafeFilename($data['filename']);
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
            FileType::PUBLIC, FileType::YTDLP => \action([DownloadController::class, 'download'], [$this, $this->filename]),
        };
    }

    public function setSafeFilename(string $filename): void
    {
        $pathinfo = \pathinfo($filename);

        $this->filename = \sprintf(
            '%s.%s',
            \substr(Str::slug($pathinfo['filename']), 0, 255 - 1 - \strlen($pathinfo['extension'])),
            $pathinfo['extension']
        );
    }
}
