<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Controller;

use Illuminate\Http\JsonResponse;
use Prex\Gifs\Application\SaveFavoriteGif\SaveFavoriteGifHandler;
use Prex\Gifs\Infrastructure\Http\Request\SaveFavoriteGifRequest;
use Prex\Gifs\Infrastructure\Http\Resource\FavoriteGifResource;
use Prex\Shared\Infrastructure\Http\AuthenticatedUser;
use Symfony\Component\HttpFoundation\Response;

final readonly class SaveFavoriteGifController
{
    public function __construct(
        private SaveFavoriteGifHandler $handler,
        private AuthenticatedUser $authenticatedUser,
    ) {}

    public function __invoke(SaveFavoriteGifRequest $request): JsonResponse
    {
        $response = $this->handler->handle(
            $request->toCommand($this->authenticatedUser->id())
        );

        return FavoriteGifResource::make($response)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
