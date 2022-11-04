<?php

namespace App\Jobs;

use App\Enums\Queue;
use App\Support\Reddit\PostProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ProcessRedditPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected readonly array $data
    ) {
        $this->queue = Queue::DOWNLOAD->value;
    }

    public function handle(PostProcessor $postProcessor): void
    {
        $postProcessor->process($this->data);
    }
}
