<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Controller;

use Illuminate\Http\JsonResponse;
use Prex\Gifs\Application\FindGif\FindGifHandler;
use Prex\Gifs\Application\FindGif\FindGifQuery;
use Prex\Gifs\Infrastructure\Http\Resource\GifResource;
use Symfony\Component\HttpFoundation\Response;

final readonly class FindGifController
{
    public function __construct(private FindGifHandler $handler) {}

    public function __invoke(string $gifId): JsonResponse
    {
        $gif = $this->handler->handle(new FindGifQuery($gifId));

        return GifResource::make($gif)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
