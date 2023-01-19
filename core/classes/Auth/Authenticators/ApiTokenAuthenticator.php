<?php

namespace Ox\Core\Auth\Authenticators;

use Exception;
use InvalidArgumentException;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\StatelessAuthBadge;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Mediboard\Admin\Repositories\AccessTokenRepository;
use Ox\Mediboard\System\CUserAuthentication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authentication with an AccessToken.
 * Extracting the token from the request and using it.
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public const TOKEN_HEADER_KEY = 'X-OXAPI-KEY';

    /** @var AccessTokenRepository */
    private $repository;

    /** @var UserProviderInterface */
    private $user_provider;

    /**
     * @param AccessTokenRepository $repository
     * @param UserProviderInterface $user_provider
     *
     * @throws InvalidArgumentException
     */
    public function __construct(AccessTokenRepository $repository, UserProviderInterface $user_provider)
    {
        if (!($user_provider instanceof UserProvider)) {
            throw new InvalidArgumentException('Not supported');
        }

        $this->repository = $repository;
        $this->user_provider = $user_provider;
    }

    public function supports(Request $request): ?bool
    {
        return !$this->isRequestPublic($request) && $request->headers->has(self::TOKEN_HEADER_KEY);
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request)
    {
        $api_token = $request->headers->get(self::TOKEN_HEADER_KEY);

        if ((null === $api_token) || ('' === $api_token)) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $token = $this->repository->findByHash($api_token);

        if (($token === null) || !$token->isValid($request)) {
            throw new CustomUserMessageAuthenticationException('Invalid token provided');
        }

        try {
            $token->useIt();
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('An error occurred');
        }

        // Todo: Voir si l'on peut s'en passer (pas de session ici, mais CAppUI::$token_xxx)
        // Use the SESSION
        //$token->applyParams();

        try {
            $user = $token->loadRefUser();

            if (!$user || !$user->_id) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('User not found');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->user_username, [$this->user_provider, 'loadUserByIdentifier']),
            [new LogAuthBadge(CUserAuthentication::AUTH_METHOD_TOKEN), new StatelessAuthBadge()]
        );
    }
}
