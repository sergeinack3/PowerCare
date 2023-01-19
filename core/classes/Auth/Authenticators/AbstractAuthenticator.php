<?php

namespace Ox\Core\Auth\Authenticators;

use Ox\Core\Auth\Exception\AuthenticationFailedException;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator as VendorAuthenticator;

abstract class AbstractAuthenticator extends VendorAuthenticator
{
    use RequestHelperTrait;

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     * @throws AuthenticationFailedException
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception->getPrevious() instanceof UserNotFoundException) {
            throw AuthenticationFailedException::invalidCredentials();
        }

        // Let the request goes on, the HttpException will be thrown in AuthenticationListener.
        return null;
    }
}
