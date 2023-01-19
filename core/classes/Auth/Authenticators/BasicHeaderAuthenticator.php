<?php

namespace Ox\Core\Auth\Authenticators;

use Exception;
use InvalidArgumentException;
use Ox\Core\Auth\Badges\IncrementLoginAttemptsBadge;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\ResetLoginAttemptsBadge;
use Ox\Core\Auth\Badges\StatelessAuthBadge;
use Ox\Core\Auth\Badges\WeakPasswordBadge;
use Ox\Core\Auth\Checkers\CredentialsCheckerInterface;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Mediboard\System\CUserAuthentication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Authentication with Basic headers.
 * Extracting username and password.
 */
class BasicHeaderAuthenticator extends AbstractAuthenticator
{
    private const BASIC_REGEXP = '/^Basic\s(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/';
    private const LOGIN_REGEXP = '/^(?<username>[^:]+):(?<password>.+)$/';

    /** @var UserProviderInterface */
    private $user_provider;

    /** @var CredentialsCheckerInterface */
    private $credentials_checker;

    /**
     * @param UserProviderInterface       $user_provider
     * @param CredentialsCheckerInterface $credentials_checker
     *
     * @throws Exception
     */
    public function __construct(UserProviderInterface $user_provider, CredentialsCheckerInterface $credentials_checker)
    {
        if (!($user_provider instanceof UserProvider)) {
            throw new InvalidArgumentException('Not supported');
        }

        $this->user_provider       = $user_provider;
        $this->credentials_checker = $credentials_checker;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        $basic = $request->headers->get('Authorization');

        return !$this->isRequestPublic($request) && ($basic && (strpos($basic, 'Basic') === 0));
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request)
    {
        $basic = $request->headers->get('Authorization');

        $match = [];
        if (!preg_match(self::BASIC_REGEXP, $basic, $match)) {
            throw new CustomUserMessageAuthenticationException('Invalid Basic header');
        }

        $b64         = explode(' ', $basic)[1];
        $credentials = base64_decode($b64);

        $match = [];
        if (preg_match(self::LOGIN_REGEXP, $credentials, $match)) {
            $username = $match['username'];
            $password = $match['password'];
        } else {
            throw new CustomUserMessageAuthenticationException('Invalid header format');
        }

        $increment_badge     = new IncrementLoginAttemptsBadge();
        $log_auth_badge      = new LogAuthBadge(CUserAuthentication::AUTH_METHOD_BASIC);
        $weak_password_badge = new WeakPasswordBadge();

        // The same badges are passed by reference to the credentials checker and to the passport
        // in order to toggle the failure increment, the log method and the weak password.
        $this->credentials_checker->setIncrementLogAttemptsBadge($increment_badge)
                                  ->setLogAuthBadge($log_auth_badge)
                                  ->setWeakPasswordBadge($weak_password_badge);

        return new Passport(
            new UserBadge($username, [$this->user_provider, 'loadUserByIdentifier']),
            new CustomCredentials([$this->credentials_checker, 'check'], $password),
            [
                $increment_badge,
                $log_auth_badge,
                $weak_password_badge,
                new ResetLoginAttemptsBadge(),
                new StatelessAuthBadge(),
            ]
        );
    }
}
