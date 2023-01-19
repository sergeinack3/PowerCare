<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Listeners;

use Exception;
use Ox\Core\Auth\Badges\IncrementLoginAttemptsBadge;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\ResetLoginAttemptsBadge;
use Ox\Core\Auth\Badges\StatelessAuthBadge;
use Ox\Core\Auth\Badges\WeakPasswordBadge;
use Ox\Core\Auth\Exception\AuthenticationFailedException;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Core\CApp;
use Ox\Mediboard\System\Factories\UserAuthenticationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Listener allowing us to log successful and failed connections and to process the last loadings required for complete
 * authentication.
 */
class AuthenticationListener implements EventSubscriberInterface
{
    /** @var CApp */
    private $app;

    /** @var UserProviderInterface */
    private $user_provider;

    /** @var UserAuthenticationFactory */
    private $auth_factory;

    /**
     * @param CApp                      $app
     * @param UserProviderInterface     $user_provider
     * @param UserAuthenticationFactory $auth_factory
     *
     * @throws Exception
     */
    public function __construct(
        CApp                      $app,
        UserProviderInterface     $user_provider,
        UserAuthenticationFactory $auth_factory
    ) {
        if (!$user_provider instanceof UserProvider) {
            throw new Exception('Not supported');
        }

        $this->app           = $app;
        $this->user_provider = $user_provider;
        $this->auth_factory  = $auth_factory;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onSuccessfulAuth', 100],
            LoginFailureEvent::class => ['onFailedAuth', 100],
        ];
    }

    /**
     * @param LoginSuccessEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function onSuccessfulAuth(LoginSuccessEvent $event): void
    {
        /** @var Passport $passport */
        $passport = $event->getPassport();

        [
            $log_auth_badge,
            $stateless_badge,
            $reset_attempts_badge,
            $weak_password_badge,
        ] = $this->extractSuccessBadges($passport);

        $ox_user = $this->user_provider->loadOxUser($event->getUser());

        $log_auth  = ($log_auth_badge && $log_auth_badge->isEnabled());
        $stateless = ($stateless_badge && $stateless_badge->isEnabled());

        $auth = null;
        if ($log_auth && $stateless) {
            $auth = $this->auth_factory->createSuccessStateless($ox_user, $log_auth_badge);
            if ($auth !== null) {
                $auth->store();
            }
        }

        if ($reset_attempts_badge && $reset_attempts_badge->isEnabled()) {
            $ox_user->resetLoginErrorsCounter(true);
        }

        $this->app->afterAuth($ox_user, $log_auth_badge, $weak_password_badge, $auth);
    }

    /**
     * @param LoginFailureEvent $event
     *
     * @return void
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    public function onFailedAuth(LoginFailureEvent $event): void
    {
        /** @var Passport $passport */
        $passport = $event->getPassport();

        // No Passport, the Authenticator probably thrown an Exception itself.
        if ($passport === null) {
            throw AuthenticationFailedException::invalidCredentials();
        }

        [$increment_attempts_badge, $log_auth_badge] = $this->extractFailureBadges($passport);

        // Todo: Create a factice User (without related CUser) to log failed attempts with wrong username.

        $ox_user = $this->user_provider->loadOxUser($passport->getUser());
        $ox_user->tryIncrementLoginAttempts($increment_attempts_badge);

        if ($log_auth_badge && $log_auth_badge->isEnabled()) {
            $error = $this->auth_factory->createError($ox_user, $log_auth_badge->getMethod());
            if ($error !== null) {
                $error->store();
            }
        }

        throw AuthenticationFailedException::invalidCredentials();
    }

    /**
     * @param Passport $passport
     *
     * @return array<LogAuthBadge|null, StatelessAuthBadge|null, ResetLoginAttemptsBadge|null, WeakPasswordBadge|null>
     */
    private function extractSuccessBadges(Passport $passport): array
    {
        return [
            $passport->getBadge(LogAuthBadge::class),
            $passport->getBadge(StatelessAuthBadge::class),
            $passport->getBadge(ResetLoginAttemptsBadge::class),
            $passport->getBadge(WeakPasswordBadge::class),
        ];
    }

    /**
     * @param Passport $passport
     *
     * @return array<IncrementLoginAttemptsBadge|null, LogAuthBadge|null>
     */
    private function extractFailureBadges(Passport $passport): array
    {
        return [
            $passport->getBadge(IncrementLoginAttemptsBadge::class),
            $passport->getBadge(LogAuthBadge::class),
        ];
    }
}
