<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Giphy;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Prex\Gifs\Domain\Exception\GifCatalogUnavailable;
use Prex\Gifs\Domain\Exception\GifNotFound;
use Prex\Gifs\Domain\Gif;
use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Domain\GifId;
use Prex\Gifs\Domain\GifSearchResult;
use Prex\Gifs\Domain\SearchCriteria;
use Throwable;

/**
 * Adapter (driven side) of the GifCatalog port, backed by the GIPHY API.
 *
 * Responsibilities:
 *  - speak HTTP (base url, api key, timeouts, retries);
 *  - translate payloads through {@see GiphyGifMapper};
 *  - translate transport failures into domain exceptions, so that no
 *    `RequestException` ever reaches a use case.
 *
 * @see https://developers.giphy.com/docs/api/
 */
final readonly class GiphyGifCatalog implements GifCatalog
{
    public function __construct(
        private HttpClient $http,
        private GiphyGifMapper $mapper,
        private GiphyConfiguration $config,
    ) {}

    public function search(SearchCriteria $criteria): GifSearchResult
    {
        $response = $this->get('/gifs/search', [
            'q' => $criteria->term->value,
            'limit' => $criteria->pagination->limit,
            'offset' => $criteria->pagination->offset,
            'rating' => $this->config->rating,
            'lang' => $this->config->language,
        ]);

        $this->failIfNotSuccessful($response);

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        $data = is_array($body['data'] ?? null) ? $body['data'] : [];
        $pagination = is_array($body['pagination'] ?? null) ? $body['pagination'] : [];

        $gifs = [];

        foreach ($data as $item) {
            if (is_array($item) && isset($item['id'])) {
                $gifs[] = $this->mapper->toDomain($item);
            }
        }

        return new GifSearchResult(
            gifs: $gifs,
            pagination: $criteria->pagination,
            totalCount: (int) ($pagination['total_count'] ?? count($gifs)),
        );
    }

    public function findById(GifId $id): Gif
    {
        $response = $this->get('/gifs/'.urlencode($id->value));

        if ($response->status() === 404) {
            throw GifNotFound::withId($id);
        }

        $this->failIfNotSuccessful($response);

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        $data = $body['data'] ?? null;

        // GIPHY answers 200 with an empty `data` object for unknown ids.
        if (! is_array($data) || ! isset($data['id'])) {
            throw GifNotFound::withId($id);
        }

        return $this->mapper->toDomain($data);
    }

    /**
     * Performs the call, turning any transport level failure into a domain
     * exception. Interpreting the status code is left to the caller.
     *
     * @param  array<string, mixed>  $query
     */
    private function get(string $path, array $query = []): Response
    {
        try {
            $response = $this->http
                ->baseUrl($this->config->baseUrl)
                ->timeout($this->config->timeout)
                ->connectTimeout($this->config->connectTimeout)
                ->retry(
                    $this->config->retries,
                    $this->config->retryDelayInMilliseconds,
                    $this->shouldRetry(),
                    throw: false
                )
                ->acceptJson()
                ->get($path, ['api_key' => $this->config->apiKey, ...$query]);
        } catch (ConnectionException $exception) {
            throw GifCatalogUnavailable::because('the provider could not be reached', $exception);
        } catch (RequestException $exception) {
            throw GifCatalogUnavailable::because('the provider returned an error response', $exception);
        } catch (Throwable $exception) {
            throw GifCatalogUnavailable::because($exception->getMessage(), $exception);
        }

        return $response;
    }

    /**
     * Only transient failures deserve a retry: a 404 or a 401 would give the
     * same answer on every attempt.
     */
    private function shouldRetry(): callable
    {
        return static function (Throwable $exception): bool {
            if ($exception instanceof ConnectionException) {
                return true;
            }

            if ($exception instanceof RequestException && $exception->response !== null) {
                return $exception->response->status() === 429 || $exception->response->serverError();
            }

            return false;
        };
    }

    private function failIfNotSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw GifCatalogUnavailable::because(sprintf(
            'the provider responded with HTTP %d',
            $response->status()
        ));
    }
}
