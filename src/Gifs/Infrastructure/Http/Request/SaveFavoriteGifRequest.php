<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Prex\Gifs\Application\SaveFavoriteGif\SaveFavoriteGifCommand;
use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Infrastructure\Http\Request\NormalizesInputKeys;

final class SaveFavoriteGifRequest extends FormRequest
{
    use NormalizesInputKeys;

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'gif_id' => ['required', 'string', 'max:'.GifId::MAX_LENGTH, 'regex:/^[A-Za-z0-9_-]+$/'],
            'alias' => ['required', 'string', 'max:'.Alias::MAX_LENGTH],
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toCommand(int $authenticatedUserId): SaveFavoriteGifCommand
    {
        return new SaveFavoriteGifCommand(
            gifId: (string) $this->input('gif_id'),
            alias: (string) $this->input('alias'),
            userId: (int) $this->input('user_id'),
            authenticatedUserId: $authenticatedUserId,
        );
    }
}
