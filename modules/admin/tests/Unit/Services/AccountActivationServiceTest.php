<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Tests\Unit\Services;

use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Exception\CouldNotActivateAccount;
use Ox\Mediboard\Admin\Generators\CUserGenerator;
use Ox\Mediboard\Admin\Services\AccountActivationService;
use Ox\Mediboard\System\CSourceSMTP;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class AccountActivationServiceTest extends OxUnitTestCase
{
    private const EMAIL = 'toolbox@openxtrem.com';

    /** @var CUser */
    private static $user;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$user = (new CUserGenerator())->generate();
    }

    public function invalidEmailProvider(): array
    {
        return [
            'empty'              => [''],
            'invalid characters' => ['test()'],
            'invalid format'     => ['test@@test'],
        ];
    }

    public function testWhenUserDoesNotExist(): void
    {
        $this->expectExceptionObject(CouldNotActivateAccount::userNotFound());

        $user = new CUser();

        new AccountActivationService($user);
    }

    public function testWhenUserIsSuperAdmin(): void
    {
        $user = $this->getMockBuilder(CUser::class)->getMock();
        $user->expects($this->once())->method('isSuperAdmin')->willReturn(true);

        $user->_id = 'DoesNotReallyExist';

        $this->expectExceptionObject(CouldNotActivateAccount::superAdminNotAllowed());
        new AccountActivationService($user);
    }

    public function testRealTokenGeneration(): void
    {
        // Real user
        $service = new AccountActivationService(self::$user);

        // Real token
        $token = $service->generateToken();

        // Asserting that a valid token is generated is acceptable here
        $this->assertNotNull($token->_id);
    }

    public function testFakeTokenGeneration(): void
    {
        // Fake user
        $user      = $this->getMockBuilder(CUser::class)->getMock();
        $user->_id = 'DoesNotReallyExist';

        // Mocking storeToken
        $service = $this->getMockBuilder(AccountActivationService::class)
            ->setConstructorArgs([$user])
            ->onlyMethods(['storeToken'])
            ->getMock();

        // Fake token
        $token = $service->generateToken();

        // Asserting that token is a CViewAccessToken instance is implicit. We just check ID is null (because of mock).
        $this->assertNull($token->_id);
    }

    public function testTokenGenerationFailsIfUnableToResetPassword(): void
    {
        $user = $this->getMockBuilder(CUser::class)->getMock();
        $user->expects($this->any())->method('store')->willReturn('Unable to reset TEST password');

        $user->_id = 'DoesNotReallyExist';

        $service = new AccountActivationService($user);

        $this->expectExceptionObject(CouldNotActivateAccount::unableToResetPassword('Unable to reset TEST password'));
        $service->generateToken();
    }

    public function testTokenGenerationFailsIfUnableToCreateToken(): void
    {
        $user = $this->getMockBuilder(CUser::class)->getMock();
        $user->expects($this->any())->method('store')->willReturn('An error during test occurred');

        $user->_id = 'DoesNotReallyExist';

        $service = $this->getMockBuilder(AccountActivationService::class)
            ->setConstructorArgs([self::$user])
            ->onlyMethods(['storeToken'])
            ->getMock();

        $service->expects($this->once())->method('storeToken')->willThrowException(
            new TestsException('Unable to store TEST token')
        );

        $this->expectExceptionObject(CouldNotActivateAccount::unableToCreateToken('Unable to store TEST token'));
        $service->generateToken();
    }

    /**
     * @depends testRealTokenGeneration
     *
     * @throws CouldNotActivateAccount
     */
    public function testSendingEmail(): void
    {
        $source         = $this->getMockBuilder(CSourceSMTP::class)->getMock();
        $source->_id    = 'DoesNotReallyExist';
        $source->active = 1;

        $smarty = $this->getMockBuilder(CSmartyDP::class)->getMock();

        $service = new AccountActivationService(self::$user, $source);
        $result  = $service->sendTokenViaEmail(self::EMAIL, $smarty);

        $this->assertTrue($result);
    }

    public function testSendEmailWithoutSourceThrowsAnException(): void
    {
        $service = new AccountActivationService(self::$user);
        $smarty  = $this->getMockBuilder(CSmartyDP::class)->getMock();

        $this->expectExceptionObject(CouldNotActivateAccount::sourceNotFound());
        $service->sendTokenViaEmail(self::EMAIL, $smarty);
    }

    public function testSendEmailWithDisabledSourceThrowsAnException(): void
    {
        $source      = $this->getMockBuilder(CSourceSMTP::class)->getMock();
        $source->_id = 'DoesNotReallyExist';

        $smarty = $this->getMockBuilder(CSmartyDP::class)->getMock();

        $service = new AccountActivationService(self::$user);

        $this->expectExceptionObject(CouldNotActivateAccount::sourceNotFound());
        $service->sendTokenViaEmail(self::EMAIL, $smarty);
    }

    /**
     * @dataProvider invalidEmailProvider
     *
     * @param string $email
     *
     * @throws CouldNotActivateAccount
     */
    public function testSendEmailWithInvalidEmailFails(string $email): void
    {
        $source         = $this->getMockBuilder(CSourceSMTP::class)->getMock();
        $source->_id    = 'DoesNotReallyExist';
        $source->active = 1;

        $smarty = $this->getMockBuilder(CSmartyDP::class)->getMock();

        $service = new AccountActivationService(self::$user, $source);

        $this->expectExceptionObject(CouldNotActivateAccount::invalidEmail($email));
        $service->sendTokenViaEmail($email, $smarty);
    }

    public function testSendEmailHandleSourceException(): void
    {
        $source         = $this->getMockBuilder(CSourceSMTP::class)->getMock();
        $source->_id    = 'DoesNotReallyExist';
        $source->active = 1;

        $source->expects($this->once())->method('send')->willThrowException(
            new TestsException('Unable to send TEST email')
        );

        $smarty = $this->getMockBuilder(CSmartyDP::class)->getMock();

        $service = new AccountActivationService(self::$user, $source);

        $this->expectExceptionObject(CouldNotActivateAccount::unableToSendEmail('Unable to send TEST email'));
        $service->sendTokenViaEmail(self::EMAIL, $smarty);
    }
}
