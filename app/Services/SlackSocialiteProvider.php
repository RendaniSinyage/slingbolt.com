<?php

namespace App\Services;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class SlackSocialiteProvider extends AbstractProvider
{
    protected $scopes = ['chat:write', 'channels:read'];
    protected $scopeSeparator = ',';

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://slack.com/oauth/v2/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://slack.com/api/oauth.v2.access';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/auth.test', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['user_id'],
            'email' => null, // Bot tokens don't have user email
            'name' => $user['user'],
        ]);
    }

    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }
}