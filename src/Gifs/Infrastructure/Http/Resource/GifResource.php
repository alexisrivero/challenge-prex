<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Prex\Gifs\Domain\Gif;
use Prex\Gifs\Domain\GifImage;

/**
 * @mixin Gif
 */
final class GifResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Gif $gif */
        $gif = $this->resource;

        return [
            'id' => $gif->id->value,
            'title' => $gif->title,
            'url' => $gif->url,
            'rating' => $gif->rating,
            'username' => $gif->username,
            'images' => [
                'original' => $this->image($gif->original),
                'preview' => $this->image($gif->preview),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function image(?GifImage $image): ?array
    {
        if ($image === null) {
            return null;
        }

        return [
            'url' => $image->url,
            'width' => $image->width,
            'height' => $image->height,
            'size_in_bytes' => $image->sizeInBytes,
        ];
    }
}
