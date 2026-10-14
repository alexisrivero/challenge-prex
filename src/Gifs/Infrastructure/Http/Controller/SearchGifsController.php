<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Controller;

use Illuminate\Http\JsonResponse;
use Prex\Gifs\Application\SearchGifs\SearchGifsHandler;
use Prex\Gifs\Infrastructure\Http\Request\SearchGifsRequest;
use Prex\Gifs\Infrastructure\Http\Resource\GifResource;
use Symfony\Component\HttpFoundation\Response;

final readonly class SearchGifsController
{
    public function __construct(private SearchGifsHandler $handler) {}

    public function __invoke(SearchGifsRequest $request): JsonResponse
    {
        $result = $this->handler->handle($request->toQuery());

        return GifResource::collection($result->gifs)
            ->additional(['meta' => [
                'count' => $result->count(),
                'total_count' => $result->totalCount,
                'limit' => $result->pagination->limit,
                'offset' => $result->pagination->offset,
            ]])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
