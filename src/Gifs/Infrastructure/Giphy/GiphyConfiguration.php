<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Giphy;

use InvalidArgumentException;

/**
 * Typed view of `config/giphy.php`, so the adapter never reads global state.
 */
final readonly class GiphyConfiguration
{
    public function __construct(
        public string $baseUrl,
        public string $apiKey,
        public int $timeout = 10,
        public int $connectTimeout = 5,
        public int $retries = 2,
        public int $retryDelayInMilliseconds = 200,
        public string $rating = 'g',
        public string $language = 'en',
    ) {
        if ($this->apiKey === '') {
            throw new InvalidArgumentException(
                'The GIPHY api key is missing. Set GIPHY_API_KEY in your .env file.'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            baseUrl: rtrim((string) ($config['base_url'] ?? ''), '/'),
            apiKey: (string) ($config['api_key'] ?? ''),
            timeout: (int) ($config['timeout'] ?? 10),
            connectTimeout: (int) ($config['connect_timeout'] ?? 5),
            retries: max(1, (int) ($config['retries'] ?? 2)),
            retryDelayInMilliseconds: (int) ($config['retry_delay'] ?? 200),
            rating: (string) ($config['rating'] ?? 'g'),
            language: (string) ($config['language'] ?? 'en'),
        );
    }
}
