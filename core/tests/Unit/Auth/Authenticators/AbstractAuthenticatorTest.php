<?php

namespace Ox\Core\Tests\Unit\Auth\Authenticators;

use InvalidArgumentException;
use Ox\Core\Auth\Authenticators\AbstractAuthenticator;
use Ox\Core\Auth\Authenticators\ApiTokenAuthenticator;
use Ox\Core\Auth\Exception\AuthenticationFailedException;
use Ox\Core\Auth\Providers\TestUserProvider;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Mediboard\Admin\Repositories\AccessTokenRepository;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class AbstractAuthenticatorTest extends OxUnitTestCase
{
    abstract protected function getObject(?UserProviderInterface $provider = null): AbstractAuthenticator;

    /**
     * @dataProvider userProviderProvider
     *
     * @param UserProviderInterface $provider
     * @param bool                  $exception
     *
     * @return void
     */
    public function testOnlySupportsUserProvider(UserProviderInterface $provider, bool $exception): void
    {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->getObject($provider);
    }

    /**
     * @return void
     */
    public function testOnAuthenticationSuccessReturnsNull(): void
    {
        $auth = $this->getObject();

        $this->assertNull(
            $auth->onAuthenticationSuccess(new Request(), $this->createMock(TokenInterface::class), 'test')
        );
    }

    /**
     * @return void
     */
    public function testOnAuthenticationFailureCatchesUserNotFound(): void
    {
        $this->expectExceptionObject(AuthenticationFailedException::invalidCredentials());

        $auth = $this->getObject();

        $exception = new AuthenticationException('test', 0, new UserNotFoundException());

        $auth->onAuthenticationFailure(new Request(), $exception);
    }

    /**
     * @return void
     */
    public function testOnAuthenticationFailureReturnsNull(): void
    {
        $auth = $this->getObject();

        $this->assertNull($auth->onAuthenticationFailure(new Request(), new AuthenticationException()));
    }

    public function userProviderProvider(): array
    {
        return [
            'UserProvider'          => [new UserProvider(), false],
            'TestUserProvider'      => [new TestUserProvider(), false],
            'UserProviderInterface' => [$this->createMock(UserProviderInterface::class), true],
        ];
    }
}
