<?php
declare(strict_types=1);

namespace App\Service;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GoogleUser;

class GoogleOAuthService
{
    protected Google $provider;

    public function __construct()
    {
        // On Windows/Laragon, the cacert.pem path is often broken.
        // We inject a custom Guzzle client that disables SSL verification
        // in debug/local mode only. Production always enforces SSL.
        $httpClient = new \GuzzleHttp\Client([
            'verify' => !\Cake\Core\Configure::read('debug'),
        ]);

        // Credentials read exclusively from .env
        $this->provider = new Google([
            'clientId'     => env('GOOGLE_OAUTH_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
            'redirectUri'  => env('GOOGLE_OAUTH_REDIRECT_URI'),
        ], [
            'httpClient' => $httpClient,
        ]);
    }

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => [
                'email',
                'profile'
            ]
        ]);
    }

    public function getAccessToken(string $code): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
    }

    public function getResourceOwner(AccessToken $token): GoogleUser
    {
        /** @var GoogleUser $user */
        $user = $this->provider->getResourceOwner($token);
        return $user;
    }
}
