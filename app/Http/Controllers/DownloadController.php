<?php

namespace App\Http\Controllers;

use App\Enums\FileType;
use App\Models\File;
use Exception;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;

use GuzzleHttp\{
    Client,
    RequestOptions
};

class DownloadController extends Controller
{
    public function download(File $file, string $filename): Response|StreamedResponse
    {
        if ($file->filename !== $filename) {
            throw new NotFoundHttpException;
        }

        return match ($file->type) {
            FileType::PUBLIC => $this->downloadPublic($file),
            FileType::YTDLP => $this->downloadYtdlp($file),
            default => throw new NotFoundHttpException,
        };
    }

    protected function downloadPublic(File $file): Response
    {
        $downloadUrl = $file->data['url'];

        return new Response('', 200, [
            'X-Accel-Redirect' => '/__download/' . \preg_replace('~^(https?)://~', '$1/', $downloadUrl),
        ]);
    }

    protected function downloadYtdlp(File $file): StreamedResponse
    {
        $process = new Process([
            \env('YT_DLP'),
            '--no-playlist',
            '--no-download',
            '--no-progress',
            '--dump-json',
            $file->url,
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

        $client = new Client;

        $response = $client->request('GET', $data['url'], [
            RequestOptions::HEADERS => $data['http_headers'],
            RequestOptions::STREAM => true,
        ]);

        $body = $response->getBody();

        return new StreamedResponse(
            function () use ($body) {
                while (! $body->eof()) {
                    echo $body->read(4 * 1024);
                }
            },
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
