<?php

namespace Ox\Core\Auth\Authenticators;

use Exception;
use InvalidArgumentException;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Core\CAppUI;
use Ox\Core\Sessions\CSessionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Throwable;

/**
 * Legacy Session Authenticator using existing session cookie set by the legacy app.
 */
class LegacySessionAuthenticator extends AbstractAuthenticator
{
    /** @var UserProviderInterface */
    private $user_provider;

    /** @var CSessionManager */
    private $session_manager;

    /**
     * @param UserProviderInterface $user_provider
     * @param CSessionManager       $session_manager
     *
     * @throws Exception
     */
    public function __construct(UserProviderInterface $user_provider, CSessionManager $session_manager)
    {
        if (!($user_provider instanceof UserProvider)) {
            throw new InvalidArgumentException('Not supported');
        }

        $this->user_provider   = $user_provider;
        $this->session_manager = $session_manager;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function supports(Request $request): ?bool
    {
        return !$this->isRequestPublic($request) && $request->cookies->has($this->session_manager->getSessionName());
    }

    /**
     * @inheritDoc
     * @throws CustomUserMessageAuthenticationException
     */
    public function authenticate(Request $request)
    {
        try {
            // Init session
            $this->session_manager->init();
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('An error occurred');
        }

        if (!$this->session_manager->userHasSession()) {
            throw new CustomUserMessageAuthenticationException('No session provided');
        }

        try {
            $ox_user = $this->user_provider->loadOxUserById(CAppUI::$instance->user_id);
        } catch (Throwable $t) {
            throw new CustomUserMessageAuthenticationException('An error occurred');
        }

        return new SelfValidatingPassport(
            new UserBadge($ox_user->user_username, [$this->user_provider, 'loadUserByIdentifier'])
        );
    }
}
