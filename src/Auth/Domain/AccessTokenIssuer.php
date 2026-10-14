<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

/**
 * Port: the OAuth2 authorisation server that mints access tokens.
 *
 * The domain only cares about "give me a token for this user"; whether that is
 * Passport, Keycloak or an in-memory double is an infrastructure concern.
 */
interface AccessTokenIssuer
{
    public function issueFor(User $user): AccessToken;
}
