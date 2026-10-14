<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Prex\Auth\Application\Login\LoginCommand;

final class LoginRequest extends FormRequest
{
    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Translate the HTTP payload into the use case input.
     */
    public function toCommand(): LoginCommand
    {
        return new LoginCommand(
            email: (string) $this->input('email'),
            password: (string) $this->input('password'),
        );
    }
}
