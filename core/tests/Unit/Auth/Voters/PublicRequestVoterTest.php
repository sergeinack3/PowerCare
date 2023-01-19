<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Voters;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Auth\Voters\PublicRequestVoter;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PublicRequestVoterTest extends OxUnitTestCase
{
    /**
     * @dataProvider subjectProvider
     *
     * @param string $subject
     * @param bool   $expected
     *
     * @return void
     */
    public function testVoterOnlySupportsRequestObjects(string $subject, bool $expected): void
    {
        $voter = new PublicRequestVoter();
        $this->assertEquals($expected, $voter->supportsType($subject));
    }

    /**
     * @dataProvider attributeProvider
     *
     * @param string $attribute
     * @param bool   $expected
     *
     * @return void
     */
    public function testVoterOnlySupportsRoleApiUserAsAttribute(string $attribute, bool $expected): void
    {
        $voter = new PublicRequestVoter();
        $this->assertEquals($expected, $voter->supportsAttribute($attribute));
    }

    /**
     * @dataProvider requestProvider
     *
     * @param TokenInterface $token
     * @param Request        $request
     * @param int            $expected
     *
     * @return void
     */
    public function testVoterOnlyValidatePublicRequest(TokenInterface $token, Request $request, int $expected): void
    {
        $voter = new PublicRequestVoter();
        $this->assertEquals($expected, $voter->vote($token, $request, ['ROLE_API_USER']));
    }

    private function getTokenObject(): TokenInterface
    {
        return $this->getMockBuilder(TokenInterface::class)->getMock();
    }

    public function subjectProvider(): array
    {
        return [
            'SF request class'  => [Request::class, true],
            'OX request class'  => [RequestApi::class, false],
            'string'            => [get_debug_type('test'), false],
            'int'               => [get_debug_type(1), false],
            'null'              => [get_debug_type(null), false],
            'bool'              => [get_debug_type(true), false],
            'float'             => [get_debug_type(1.0), false],
            'array'             => [get_debug_type([]), false],
            'resource'          => ['resource (stream)', false],
            'resource (closed)' => ['resource (closed)', false],
            'anonymous class'   => [
                get_debug_type(
                    new class {
                    }
                ),
                false,
            ],
        ];
    }

    public function attributeProvider(): array
    {
        return [
            'ROLE_API_USER' => ['ROLE_API_USER', true],
            'role_api_user' => ['role_api_user', false],
            'ROLE_USER'     => ['ROLE_USER', false],
            'empty'         => ['', false],
        ];
    }

    public function requestProvider(): array
    {
        $token = $this->getTokenObject();

        $std = new Request();

        $public = new Request();
        $public->attributes->set('public', true);

        $null = new Request();
        $null->attributes->set('public', null);

        $non_empty = new Request();
        $non_empty->attributes->set('public', [1, 2, 3]);

        return [
            'public'    => [$token, $public, VoterInterface::ACCESS_GRANTED],
            'standard'  => [$token, $std, VoterInterface::ACCESS_DENIED],
            'null'      => [$token, $null, VoterInterface::ACCESS_DENIED],
            'non-empty' => [$token, $non_empty, VoterInterface::ACCESS_DENIED],
        ];
    }
}
