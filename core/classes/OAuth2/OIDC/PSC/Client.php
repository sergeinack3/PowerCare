<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\OAuth2\OIDC\PSC;

use Exception;
use Jumbojett\Client as OidcClient;
use Jumbojett\Config;
use Jumbojett\Jwt;
use Jumbojett\OpenIDConnectClientException;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\OAuth2\OIDC\TokenSet;
use Ox\Core\Sessions\CSessionHandler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * OIDC client wrapped around PSC.
 */
class Client
{
    // Connection timeout of 2 seconds seems a little short for PSC service.
    private const CONNECTION_TIMEOUT = 3;
    private const TIMEOUT            = 5;

    private const LEEWAY = 300;

    private OidcClient $client;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $options = CAppUI::getPSCOptions();

        $provider_url = 'https://auth.bas.psc.esante.gouv.fr/auth/realms/esante-wallet';

        $config = new Config($provider_url, $options['clientId'], $options['clientSecret']);
        $config->setIssuer($provider_url);

        $config->setConnectionTimeout(self::CONNECTION_TIMEOUT);
        $config->setTimeout(self::TIMEOUT);

        $config->setWellKnownConfigUrl($provider_url . '/.well-known/wallet-openid-configuration');
        $config->addAuthParams(['acr_values' => 'eidas1']);
        $config->addScopes(['openid', 'scope_all']);
        $config->setLeeway(self::LEEWAY);

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
        return $this->client->requestAuthorization(CAppUI::getPSCOptions()['redirectUri']);
    }

    /**
     * @param string $code
     * @param string $state
     *
     * @return void
     * @throws Exception
     */
    public function requestTokens(string $code, string $state): void
    {
        $this->client->requestTokens(CAppUI::getPSCOptions()['redirectUri'], $code, $state, null);
    }

    private function hasRefreshTokenExpired(): bool
    {
        $refresh_token = $this->client->getRefreshToken();

        if ($refresh_token === null) {
            return false;
        }

        $token = new Jwt($refresh_token);

        return $token->hasExpired(self::LEEWAY);
    }

    public function refreshToken(string $refresh_token, TokenSet $token_set = null): void
    {
        $this->client->refreshToken($refresh_token);

        if ($token_set !== null) {
            $token_set->setAccessToken($this->client->getAccessToken());
            $token_set->setRefreshToken($this->client->getRefreshToken());
        }
    }

    public function getTokenSet(): TokenSet
    {
        return new TokenSet(
            TokenSet::PSC_TYPE,
            $this->client->getAccessToken(),
            $this->client->getRefreshToken(),
            $this->client->getIdToken()
        );
    }

    /**
     * @param string $id_token
     * @param bool   $redirect
     *
     * @throws OpenIDConnectClientException
     * @throws Exception
     */
    public function signOut(string $id_token, bool $redirect): string
    {
        $options  = CAppUI::getPSCOptions();
        $redirect = ($redirect) ? $options['redirectUri'] : null;

        return $this->client->signOut($id_token, $redirect);
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

    public static function checkSession(?TokenSet $oidc_tokens): void
    {
        if (!CAppUI::isLoginPSCEnabled()) {
            return;
        }

        if (!CAppUI::conf('admin ProSanteConnect session_mode')) {
            return;
        }

        if ($oidc_tokens === null) {
            return;
        }

        $client = new self();

        // Todo: Warn user that the PSC expired, and logout
        if ($client->hasRefreshTokenExpired()) {
            CSessionHandler::end(true);
            CApp::rip();
        }

        // Todo: Make Exceptions more distinct (notably Expired Token from An Error Occurred)
        // Todo: DO NOT invalid session if Network Exception
        // Todo: Refactor that part when client will be SF.
        try {
            $client->refreshToken($oidc_tokens->getRefreshToken(), $oidc_tokens);
        } catch (Exception $e) {
            // Token has expired (setting to NULL, because unset break the reference link)
            $oidc_tokens = null;

            CSessionHandler::end(true);
            CApp::rip();
        }
    }
}
