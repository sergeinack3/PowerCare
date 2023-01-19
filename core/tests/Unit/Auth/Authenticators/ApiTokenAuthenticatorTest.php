<?php

namespace Ox\Core\Tests\Unit\Auth\Authenticators;

use Exception;
use Ox\Core\Auth\Authenticators\AbstractAuthenticator;
use Ox\Core\Auth\Authenticators\ApiTokenAuthenticator;
use Ox\Core\Auth\Providers\UserProvider;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Admin\Repositories\AccessTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticatorTest extends AbstractAuthenticatorTest
{
    protected function getObject(?UserProviderInterface $provider = null): AbstractAuthenticator
    {
        return new ApiTokenAuthenticator(new AccessTokenRepository(), $provider ?? new UserProvider());
    }

    /**
     * @dataProvider requestProvider
     *
     * @param Request $request
     * @param bool    $expected
     *
     * @return void
     */
    public function testOnlySupportsRequestsWithTokenHeader(Request $request, bool $expected): void
    {
        $auth = new ApiTokenAuthenticator(new AccessTokenRepository(), new UserProvider());
        $this->assertEquals($expected, $auth->supports($request));
    }

    /**
     * @dataProvider emptyTokenProvider
     *
     * @param Request $request
     *
     * @return void
     */
    public function testDoesNotPermitEmptyToken(Request $request): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $auth = $this->getObject();

        $auth->authenticate($request);
    }

    /**
     * @dataProvider invalidTokenProvider
     *
     * @param AccessTokenRepository $repository
     *
     * @return void
     */
    public function testDoesNotPermitInvalidToken(AccessTokenRepository $repository): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $auth = new ApiTokenAuthenticator($repository, new UserProvider());

        $auth->authenticate($this->getValidRequest());
    }

    public function testErrorUsingTokenThrowsAnException(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $auth = new ApiTokenAuthenticator($this->mockRepository(true, true, true, null), new UserProvider());
        $auth->authenticate($this->getValidRequest());
    }

    /**
     * @dataProvider invalidUserFromTokenProvider
     *
     * @param AccessTokenRepository $repository
     *
     * @return void
     */
    public function testFailingToRetrieveUserFromTokenThrowsAnException(AccessTokenRepository $repository): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $auth = new ApiTokenAuthenticator($repository, new UserProvider());
        $auth->authenticate($this->getValidRequest());
    }

    public function testAuthenticationReturnsPassport(): void
    {
        $valid_user                = new CUser();
        $valid_user->_id           = 'test';
        $valid_user->user_username = 'test';

        $auth = new ApiTokenAuthenticator($this->mockRepository(true, true, false, $valid_user), new UserProvider());
        $this->assertInstanceOf(SelfValidatingPassport::class, $auth->authenticate($this->getValidRequest()));
    }

    private function getValidRequest(): Request
    {
        $request = new Request();
        $request->headers->set(ApiTokenAuthenticator::TOKEN_HEADER_KEY, 'test');

        return $request;
    }

    public function requestProvider(): array
    {
        $invalid = new Request();

        $public = $this->getValidRequest();
        $public->attributes->set('public', true);

        return [
            'request with token header'            => [$this->getValidRequest(), true],
            'request with token header but public' => [$public, false],
            'request without token header'         => [$invalid, false],
        ];
    }

    public function emptyTokenProvider(): array
    {
        $null_token = new Request();
        $null_token->headers->set(ApiTokenAuthenticator::TOKEN_HEADER_KEY, null);

        $empty_token = new Request();
        $empty_token->headers->set(ApiTokenAuthenticator::TOKEN_HEADER_KEY, '');

        return [
            'null token'  => [$null_token],
            'empty token' => [$empty_token],
        ];
    }

    public function invalidTokenProvider(): array
    {
        return [
            'not found token' => [$this->mockRepository(false, false, false, null)],
            'invalid token'   => [$this->mockRepository(true, false, false, null)],
        ];
    }

    public function invalidUserFromTokenProvider(): array
    {
        return [
            'null user'  => [$this->mockRepository(true, true, false, null)],
            'empty user' => [$this->mockRepository(true, true, false, new CUser())],
        ];
    }

    private function mockRepository(
        bool   $returns_token,
        bool   $is_valid,
        bool   $use_it_exception,
        ?CUser $user
    ): AccessTokenRepository {
        $repository = $this->getMockBuilder(AccessTokenRepository::class)
                           ->getMock();

        if ($returns_token) {
            $token = $this->getMockBuilder(CViewAccessToken::class)
                          ->disableOriginalConstructor()
                          ->getMock();

            $token->expects($this->any())->method('isValid')->willReturn($is_valid);

            if ($use_it_exception) {
                $token->expects($this->any())->method('useIt')->willThrowException(new Exception());
            }

            $token->expects($this->any())->method('loadRefUser')->willReturn($user);

            $repository->expects($this->any())->method('findByHash')->willReturn($token);
        } else {
            $repository->expects($this->any())->method('findByHash')->willReturn(null);
        }

        return $repository;
    }
}
