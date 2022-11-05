<?php

namespace App\Console\Commands\Media\Reddit\Parse;

use App\Jobs\ProcessRedditPost;
use App\Support\Reddit\RedditApi;
use Illuminate\Console\Command;

class Top extends Command
{
    protected $signature = 'media:reddit:parse:top {subreddit} {--limit=} {--period=month}';

    public function handle(
        RedditApi $redditApi
    ): int {
        $subreddit = \preg_replace(
            '~^r/~',
            '',
            $this->argument('subreddit')
        );
        $limit = \transform(
            $this->option('limit'),
            \intval(...),
            \PHP_INT_MAX
        );

        $after = null;

        $counter = 0;
        while (true) {
            $listing = $redditApi->listingTop(
                $subreddit,
                time: $this->option('period'),
                after: $after
            );

            foreach ($listing->children as $child) {
                $this->info(
                    \sprintf(
                        '[%6d] %s: %s',
                        ++$counter,
                        $child['data']['id'],
                        $child['data']['title'],
                    ),
                );

                \dispatch(
                    new ProcessRedditPost($child['data'])
                );

                if ($counter >= $limit) {
                    break 2;
                }
            }

            if ($listing->after === null) {
                break;
            }

            $after = $listing->after;
        }

        return Command::SUCCESS;
    }
}
