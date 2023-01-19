<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\OAuth2\OIDC\FC;

use Exception;
use Jumbojett\Client as OidcClient;
use Jumbojett\Config;
use Jumbojett\OpenIDConnectClientException;
use Ox\Core\CAppUI;
use Ox\Core\OAuth2\OIDC\TokenSet;
use Symfony\Component\HttpClient\HttpClient;

/**
 * OIDC client wrapped around FC.
 */
class Client
{
    private const CONNECTION_TIMEOUT = 3;
    private const TIMEOUT            = 5;

    private OidcClient $client;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $options = CAppUI::getFCOptions();

        // Todo: Change for Production endpoints
        $provider_url = 'https://fcp.integ01.dev-franceconnect.fr';

        $config = new Config($provider_url, $options['clientId'], $options['clientSecret']);
        $config->setIssuer($provider_url);

        $config->setConnectionTimeout(self::CONNECTION_TIMEOUT);
        $config->setTimeout(self::TIMEOUT);

        $config->setAuthorizationEndpointUrl($provider_url . '/api/v1/authorize');
        $config->setTokenEndpointUrl($provider_url . '/api/v1/token');
        $config->setUserInfoEndpointUrl($provider_url . '/api/v1/userinfo');
        $config->setLogoutEndpointUrl($provider_url . '/api/v1/logout');

        $config->setStateAndNonceInRedirectUri(true);

        $config->setTokenEndpointAuthMethodsSupported(['client_secret_post']);

        $config->addAuthParams(['acr_values' => 'eidas1']);
        $config->addScopes(['openid', 'identite_pivot', 'email']);

        $config->addClaimsValidator(
            function (OidcClient $client, array $claims): bool {
                $acr = ($claims['acr']) ?? null;

                return $acr === 'eidas1';
            }
        );

        $http_client = HttpClient::create();

        $this->client = new OidcClient($http_client, $config);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function requestAuthorization(): string
    {
        return $this->client->requestAuthorization(CAppUI::getFCOptions()['redirectUri']);
    }

    /**
     * @param string $code
     * @param string $state
     * @param string $nonce
     *
     * @return void
     * @throws Exception
     */
    public function requestTokens(string $code, string $state, string $nonce): void
    {
        $this->client->requestTokens(CAppUI::getFCOptions()['redirectUri'], $code, $state, $nonce);
    }

    public function getTokenSet(): TokenSet
    {
        return new TokenSet(
            TokenSet::FC_TYPE,
            $this->client->getAccessToken(),
            $this->client->getRefreshToken(),
            $this->client->getIdToken()
        );
    }

    /**
     * @param string $id_token
     *
     * @return string
     * @throws Exception
     */
    public function signOut(string $id_token): string
    {
        return $this->client->signOut($id_token, CAppUI::getFCOptions()['logoutRedirectUri']);
    }

    /**
     * @return array|mixed|null
     * @throws OpenIDConnectClientException
     */
    private function requestUserInfo()
    {
        return $this->client->requestUserInfo($this->client->getAccessToken());
    }

    /**
     * @return UserInfo
     * @throws OpenIDConnectClientException
     */
    public function getUserInfo(): UserInfo
    {
        $user_info = $this->requestUserInfo();

        return new UserInfo($user_info);
    }
}
