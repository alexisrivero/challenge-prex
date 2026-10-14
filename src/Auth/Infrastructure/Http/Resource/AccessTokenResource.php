<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Prex\Auth\Application\Login\LoginResponse;

/**
 * @mixin LoginResponse
 */
final class AccessTokenResource extends JsonResource
{
    public function __construct(LoginResponse $response)
    {
        parent::__construct($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var LoginResponse $response */
        $response = $this->resource;

        return [
            'access_token' => $response->accessToken,
            'token_type' => $response->tokenType,
            'expires_in' => $response->expiresIn,
            'expires_at' => $response->expiresAt->format(DATE_ATOM),
            'user' => [
                'id' => $response->userId,
                'name' => $response->userName,
                'email' => $response->userEmail,
            ],
        ];
    }
}
