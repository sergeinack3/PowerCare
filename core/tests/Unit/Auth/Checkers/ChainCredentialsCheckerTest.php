<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Checkers;

use Exception;
use Ox\Core\Auth\Checkers\ChainCredentialsChecker;
use Ox\Core\Auth\Checkers\CredentialsCheckerInterface;
use Ox\Core\Auth\Exception\CredentialsCheckException;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ChainCredentialsCheckerTest extends OxUnitTestCase
{
    /**
     * @dataProvider checkersProvider
     *
     * @param CredentialsCheckerInterface[] $checkers
     * @param bool                          $expected
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testChainResult(array $checkers, bool $expected): void
    {
        $chain = new ChainCredentialsChecker(...$checkers);

        $this->assertEquals(
            $expected,
            $chain->check('password', $this->getMockBuilder(UserInterface::class)->getMock())
        );
    }

    /**
     * @dataProvider interruptedCheckersProvider
     *
     * @param array $checkers
     * @param bool  $expected
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testCredentialsExceptionInterruptsTheChaining(array $checkers, bool $expected): void
    {
        $chain = new ChainCredentialsChecker(...$checkers);

        $this->assertEquals(
            $expected,
            $chain->check('password', $this->getMockBuilder(UserInterface::class)->getMock())
        );
    }

    /**
     * @dataProvider exceptionCheckersProvider
     *
     * @param array $checkers
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testAnotherExceptionWillNotBeCaught(array $checkers): void
    {
        $this->expectException(TestsException::class);

        $chain = new ChainCredentialsChecker(...$checkers);
        $chain->check('password', $this->getMockBuilder(UserInterface::class)->getMock());
    }

    public function checkersProvider(): array
    {
        return [
            'true'              => [
                [
                    $this->mockCredentialsChecker(true, false),
                ],
                true,
            ],
            'false'             => [
                [
                    $this->mockCredentialsChecker(false, false),
                ],
                false,
            ],
            'true true'         => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(true, true),
                ],
                true,
            ],
            'true false'        => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(false, true),
                ],
                true,
            ],
            'false true'        => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false),
                ],
                true,
            ],
            'false false'       => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false),
                ],
                false,
            ],
            'true true true'    => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(true, true),
                    $this->mockCredentialsChecker(true, true),
                ],
                true,
            ],
            'true true false'   => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(true, true),
                    $this->mockCredentialsChecker(false, true),
                ],
                true,
            ],
            'true false false'  => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(false, true),
                    $this->mockCredentialsChecker(false, true),
                ],
                true,
            ],
            'true false true'   => [
                [
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(false, true),
                    $this->mockCredentialsChecker(true, true),
                ],
                true,
            ],
            'false true true'   => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(true, true),
                ],
                true,
            ],
            'false true false'  => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false),
                    $this->mockCredentialsChecker(false, true),
                ],
                true,
            ],
            'false false true'  => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false),
                ],
                true,
            ],
            'false false false' => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false),
                ],
                false,
            ],
        ];
    }

    public function interruptedCheckersProvider(): array
    {
        return [
            'exception'             => [
                [
                    $this->mockCredentialsChecker(true, false, new CredentialsCheckException()),
                ],
                false,
            ],
            'false exception'       => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false, new CredentialsCheckException()),
                ],
                false,
            ],
            'false exception true'  => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false, new CredentialsCheckException()),
                    $this->mockCredentialsChecker(true, true),
                ],
                false,
            ],
            'false exception false' => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(false, false, new CredentialsCheckException()),
                    $this->mockCredentialsChecker(false, true),
                ],
                false,
            ],
        ];
    }

    public function exceptionCheckersProvider(): array
    {
        return [
            'exception'       => [[$this->mockCredentialsChecker(true, false, new TestsException())]],
            'false exception' => [
                [
                    $this->mockCredentialsChecker(false, false),
                    $this->mockCredentialsChecker(true, false, new TestsException()),
                ],
            ],
        ];
    }

    private function mockCredentialsChecker(bool $check, bool $never, Exception $e = null): CredentialsCheckerInterface
    {
        $checker = $this->getMockBuilder(CredentialsCheckerInterface::class)
                        ->getMock();

        if ($e === null) {
            $checker->expects(($never ? $this->never() : $this->once()))->method('check')->willReturn($check);
        } else {
            $checker->expects($this->once())->method('check')->willThrowException($e);
        }

        return $checker;
    }
}
