<?php

namespace App\Support\Reddit;

use App\Enums\MediaType;
use Closure;
use Exception;
use File as FileHelper;
use Illuminate\Contracts\Filesystem\Filesystem;
use Str;

use App\Models\{
    File,
    Media
};
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
            case isset($post['removed_by_category']):
                break;

            case null !== $downloadUrl = $data['preview']['reddit_video_preview']['fallback_url'] ?? null:
                $media = $this->createMediaFromData($data);
                $media->type = MediaType::VIDEO;
                $media->file()->associate(
                    File::fromPublicUrl($downloadUrl)
                );
                $media->save();
                break;

            case ($data['post_hint'] ?? null) === 'image':
                $this->processImage($data);
                break;

            case $data['is_gallery'] ?? false:
                $this->processGallery($data);
                break;

            case ($data['post_hint'] ?? null) === 'rich:video' && null !== $destUrl = $data['url_overridden_by_dest'] ?? null:
                $media = $this->createMediaFromData($data);
                $media->type = MediaType::VIDEO;
                $media->file()->associate(
                    File::fromYtdlpUrl($destUrl)
                );
                $media->save();
                break;

            default:
                throw new Exception('Unknown media type');
        }
    }

    protected function createMediaFromData(array $data, string $uniqueKeySuffix = ''): Media
    {
        /** @var Media $media */
        $media = Media::firstOrNew([
            'unique_key' => $data['permalink'] . $uniqueKeySuffix,
        ]);

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
        $media = $this->createMediaFromData($data);
        $media->type = \preg_match('/\.gif$/i', $data['url'])
            ? MediaType::GIF
            : MediaType::PHOTO;
        $media->file()->associate(
            File::fromPublicUrl($data['url'])
        );
        $media->save();
    }

    protected function processGallery(array $data): void
    {
        foreach ($data['gallery_data']['items'] as $i => $item) {
            $metadata = $data['media_metadata'][$item['media_id']];

            if ($metadata['e'] !== 'Image') {
                throw new Exception("Unknown gallery item type {$metadata['e']}");
            }

            $media = $this->createMediaFromData($data);
            $media->unique_key .= '+' . $i;
            $media->type = \preg_match('/\.gif$/i', $metadata['s']['u'])
                ? MediaType::GIF
                : MediaType::PHOTO;
            $media->file()->associate(
                File::fromPublicUrl($data['url'])
            );
            $media->save();
        }
    }

    protected function downloadTemporaryFile(string $url, Closure $callback): void
    {
        $directory = \storage_path('app/tmp') . '/reddit-' . Str::random(6);
        FileHelper::makeDirectory($directory, recursive: true);

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
            FileHelper::deleteDirectory($directory);
        }
    }
}
