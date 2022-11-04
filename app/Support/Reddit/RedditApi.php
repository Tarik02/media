<?php

namespace App\Support\Reddit;

use GuzzleHttp\ClientInterface;

class RedditApi
{
    public function __construct(
        protected readonly ClientInterface $http
    ) {
    }

    public function listingTop(
        string $subreddit,
        string $time = 'month',
        ?string $before = null,
        ?string $after = null,
        ?int $count = null,
        ?int $limit = null,
        ?string $show = null
    ): Listing {
        $response = $this->http->request(
            'GET',
            \sprintf(
                'https://www.reddit.com/r/%s/%s.json?%s',
                $subreddit,
                'top',
                \http_build_query([
                    'raw_json' => '1',
                    't' => $time,
                    'before' => $before,
                    'after' => $after,
                    'count' => $count,
                    'limit' => $limit,
                    'show' => $show,
                ])
            )
        );

        $data = \json_decode(
            $response->getBody()->getContents(),
            associative: true,
            flags: \JSON_THROW_ON_ERROR
        );

        return new Listing(
            after: $data['data']['after'] ?? null,
            before: $data['data']['before'] ?? null,
            children: $data['data']['children'],
        );
    }
}
