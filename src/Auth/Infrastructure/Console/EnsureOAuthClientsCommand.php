<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Console;

use Illuminate\Console\Command;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

/**
 * Idempotent bootstrap of the OAuth2 clients required by the API.
 *
 * `passport:install` is not idempotent (it creates duplicated clients on every
 * run), which makes it a poor fit for a container entrypoint. This command can
 * be executed on every boot safely.
 */
final class EnsureOAuthClientsCommand extends Command
{
    protected $signature = 'oauth:ensure-clients';

    protected $description = 'Create the OAuth2 clients required by the API if they do not exist yet';

    public function handle(ClientRepository $clients): int
    {
        $provider = (string) config('auth.guards.api.provider', 'users');

        if (! $this->hasClientWithGrant('personal_access')) {
            $clients->createPersonalAccessGrantClient(
                config('app.name').' Personal Access Client',
                $provider
            );

            $this->components->info('Personal access client created.');
        } else {
            $this->components->info('Personal access client already present.');
        }

        if (! $this->hasClientWithGrant('password')) {
            $client = $clients->createPasswordGrantClient(
                config('app.name').' Password Grant Client',
                $provider
            );

            $this->components->info('Password grant client created.');
            $this->components->twoColumnDetail('Client ID', (string) $client->getKey());
            $this->components->twoColumnDetail('Client secret', (string) ($client->plainSecret ?? '-'));
        } else {
            $this->components->info('Password grant client already present.');
        }

        return self::SUCCESS;
    }

    private function hasClientWithGrant(string $grantType): bool
    {
        return Passport::client()
            ->newQuery()
            ->where('revoked', false)
            ->get()
            ->contains(fn (Client $client): bool => $client->hasGrantType($grantType));
    }
}
