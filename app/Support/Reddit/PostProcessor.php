<?php

namespace App\Support\Reddit;

use App\Enums\MediaType;
use App\Models\Media;
use Closure;
use Exception;
use File;
use Illuminate\Contracts\Filesystem\Filesystem;
use Str;

use GuzzleHttp\{
    ClientInterface,
    RequestOptions
};

class PostProcessor
{
    public function __construct(
        protected readonly ClientInterface $client,
        protected readonly string $disk,
        protected readonly Filesystem $filesystem
    ) {
    }

    public function process(array $data): void
    {
        switch (true) {
            case ($data['post_hint'] ?? null) === 'image':
                $this->processImage($data);
                break;

            case $data['is_gallery'] ?? false:
                $this->processGallery($data);
                break;

            default:
                throw new Exception('Unknown media type');
        }
    }

    protected function createMediaFromData(array $data): Media
    {
        $media = new Media;
        $media->unique_key = $data['permalink'];
        $media->title = $data['title'];
        $media->source = \sprintf(
            'https://www.reddit.com%s',
            $data['permalink']
        );
        $media->data = $data;
        return $media;
    }

    protected function storeMediaFile(Media $media, string $filename): void
    {
        $hash = \md5_file($filename);

        $storedFileName = \sprintf(
            '%s/%s/%s/%s',
            \substr($hash, -1),
            \substr($hash, -3, 2),
            $hash,
            \basename($filename)
        );

        $this->filesystem->writeStream(
            $storedFileName,
            \fopen($filename, 'r')
        );

        $media->disk = $this->disk;
        $media->path = $storedFileName;
    }

    protected function processImage(array $data): void
    {
        $this->downloadTemporaryFile($data['url'], function (string $filename) use ($data) {
            $media = $this->createMediaFromData($data);
            if (Media::query()->where('unique_key', '=', $media->unique_key)->exists()) {
                return;
            }

            $media->type = \preg_match('/\.gif$/i', $filename)
                ? MediaType::GIF
                : MediaType::PHOTO;

            $this->storeMediaFile($media, $filename);
            $media->save();
        });
    }

    protected function processGallery(array $data): void
    {
        foreach ($data['gallery_data']['items'] as $i => $item) {
            $metadata = $data['media_metadata'][$item['media_id']];

            if ($metadata['e'] !== 'Image') {
                throw new Exception("Unknown gallery item type {$metadata['e']}");
            }

            $this->downloadTemporaryFile(
                $metadata['s']['u'],
                function (string $filename) use ($data, $i) {
                    $media = $this->createMediaFromData($data);
                    $media->unique_key .= '+' . $i;
                    if (Media::query()->where('unique_key', '=', $media->unique_key)->exists()) {
                        return;
                    }

                    $media->type = \preg_match('/\.gif$/i', $filename)
                        ? MediaType::GIF
                        : MediaType::PHOTO;

                    $this->storeMediaFile($media, $filename);
                    $media->save();
                },
            );
        }
    }

    protected function downloadTemporaryFile(string $url, Closure $callback): void
    {
        $directory = \storage_path('app/tmp') . '/reddit-' . Str::random(6);
        File::makeDirectory($directory, recursive: true);

        try {
            $filename = $directory . \DIRECTORY_SEPARATOR . \basename(\parse_url($url, \PHP_URL_PATH));

            $this->client->request(
                method: 'GET',
                uri: $url,
                options: [
                    RequestOptions::SINK => $filename,
                ]
            );

            $callback($filename);
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
