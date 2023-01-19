<?php

namespace Ox\Core\Tests\Unit\Auth\Authenticators;

use Exception;
use Ox\Core\Auth\Authenticators\AbstractAuthenticator;
use Ox\Core\Auth\Authenticators\LegacySessionAuthenticator;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Core\Sessions\CSessionManager;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LegacySessionAuthenticatorTest extends AbstractAuthenticatorTest
{
    private const VALID_SESSION_NAME   = 'test_session';
    private const INVALID_SESSION_NAME = 'invalid_test_session';

    protected function getObject(?UserProviderInterface $provider = null): AbstractAuthenticator
    {
        return new LegacySessionAuthenticator(
            $provider ?? new UserProvider(),
            $session_manager ?? $this->createMock(CSessionManager::class)
        );
    }

    /**
     * @dataProvider requestProvider
     *
     * @param Request $request
     * @param bool    $expected
     *
     * @return void
     * @throws Exception
     */
    public function testOnlySupportsRequestsWithSessionCookie(Request $request, bool $expected): void
    {
        $auth = new LegacySessionAuthenticator(new UserProvider(), $this->mockSessionManager(false));
        $this->assertEquals($expected, $auth->supports($request));
    }

    public function testOnlyAcceptsExistingSession(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $auth = new LegacySessionAuthenticator(new UserProvider(), $this->mockSessionManager(false));

        $auth->authenticate($this->getValidRequest());
    }

    public function testFailWhenNotAbleToRetrieveUser(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $provider = $this->getMockBuilder(UserProvider::class)->getMock();
        $provider->expects($this->once())->method('loadOxUserById')->willThrowException(new Exception());

        $auth = new LegacySessionAuthenticator($provider, $this->mockSessionManager(true));

        $auth->authenticate($this->getValidRequest());
    }

    public function testAuthenticationReturnsPassport(): void
    {
        $provider = $this->getMockBuilder(UserProvider::class)->getMock();
        $provider->expects($this->once())->method('loadOxUserById')->willReturnCallback(function () {
            $user                = new CUser();
            $user->user_username = 'test';

            return $user;
        });

        $auth = new LegacySessionAuthenticator($provider, $this->mockSessionManager(true));
        $this->assertInstanceOf(Passport::class, $auth->authenticate($this->getValidRequest()));
    }

    private function getValidRequest(): Request
    {
        return $this->forgeRequestWithCookie(self::VALID_SESSION_NAME);
    }

    private function forgeRequestWithCookie(string $cookie_name): Request
    {
        $request = new Request();
        $request->cookies->set($cookie_name, 'test');

        return $request;
    }

    public function requestProvider(): array
    {
        $public = $this->getValidRequest();
        $public->attributes->set('public', true);

        return [
            'request with valid cookie'            => [$this->getValidRequest(), true],
            'request with valid cookie but public' => [$public, false],
            'request without cookie'               => [new Request(), false],
            'request with invalid cookie'          => [
                $this->forgeRequestWithCookie(self::INVALID_SESSION_NAME),
                false,
            ],

        ];
    }

    private function mockSessionManager(bool $user_hash_session): CSessionManager
    {
        $session_manager = $this->getMockBuilder(CSessionManager::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $session_manager->expects($this->any())->method('getSessionName')->willReturn(self::VALID_SESSION_NAME);
        $session_manager->expects($this->any())->method('userHasSession')->willReturn($user_hash_session);

        return $session_manager;
    }
}
