<?php

namespace Ox\Core\Tests\Unit\Auth\Authenticators;

use Ox\Core\Auth\Authenticators\AbstractAuthenticator;
use Ox\Core\Auth\Authenticators\BasicHeaderAuthenticator;
use Ox\Core\Auth\Checkers\ChainCredentialsChecker;
use Ox\Core\Auth\Providers\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class BasicHeaderAuthenticatorTest extends AbstractAuthenticatorTest
{
    protected function getObject(?UserProviderInterface $provider = null): AbstractAuthenticator
    {
        return new BasicHeaderAuthenticator($provider ?? new UserProvider(), new ChainCredentialsChecker());
    }

    /**
     * @dataProvider requestProvider
     *
     * @param Request $request
     * @param bool    $expected
     *
     * @return void
     */
    public function testOnlySupportsRequestsWithBasicHeader(Request $request, bool $expected): void
    {
        $auth = new BasicHeaderAuthenticator(new UserProvider(), new ChainCredentialsChecker());
        $this->assertEquals($expected, $auth->supports($request));
    }

    /**
     * @dataProvider invalidHeaderFormat
     *
     * @param Request $request
     *
     * @return void
     */
    public function testDoesNotPermitInvalidHeaderFormat(Request $request): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $auth = $this->getObject();

        $auth->authenticate($request);
    }

    public function testAuthenticationReturnsPassport(): void
    {
        $auth = new BasicHeaderAuthenticator(new UserProvider(), new ChainCredentialsChecker());
        $this->assertInstanceOf(Passport::class, $auth->authenticate($this->getValidRequest()));
    }

    private function getValidRequest(): Request
    {
        return $this->forgeRequestWithBasicHeader('Basic ' . base64_encode('test:test'));
    }

    private function forgeRequestWithBasicHeader(?string $header): Request
    {
        $request = new Request();
        $request->headers->set('Authorization', $header);

        return $request;
    }

    public function requestProvider(): array
    {
        $public = $this->getValidRequest();
        $public->attributes->set('public', true);

        return [
            'request with valid basic header'            => [$this->getValidRequest(), true],
            'request with valid basic header but public' => [$public, false],
            'request without basic header'               => [new Request(), false],
            'request with null basic header'             => [$this->forgeRequestWithBasicHeader(null), false],
            'request with empty basic header'            => [$this->forgeRequestWithBasicHeader(''), false],
            'request with invalid basic header'          => [$this->forgeRequestWithBasicHeader('notBasic'), false],

        ];
    }

    public function invalidHeaderFormat(): array
    {
        return [
            'request with incomplete basic header' => [$this->forgeRequestWithBasicHeader('Basic'), false],
            'request with non-base64 header'       => [$this->forgeRequestWithBasicHeader('Basic test@'), false],
            'request with invalid header format'   => [
                $this->forgeRequestWithBasicHeader(
                    'Basic ' . base64_encode('test:')
                ),
                false,
            ],
        ];
    }
}
