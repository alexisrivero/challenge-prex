<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Prex\Gifs\Application\SaveFavoriteGif\SaveFavoriteGifResponse;

/**
 * @mixin SaveFavoriteGifResponse
 */
final class FavoriteGifResource extends JsonResource
{
    public function __construct(SaveFavoriteGifResponse $response)
    {
        parent::__construct($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SaveFavoriteGifResponse $response */
        $response = $this->resource;
        $favorite = $response->favorite;

        return [
            'id' => $favorite->id->value,
            'user_id' => $favorite->userId->value,
            'gif_id' => $favorite->gifId->value,
            'alias' => $favorite->alias->value,
            'saved_at' => $favorite->savedAt->format(DATE_ATOM),
            'gif' => GifResource::make($response->gif)->toArray($request),
        ];
    }
}
