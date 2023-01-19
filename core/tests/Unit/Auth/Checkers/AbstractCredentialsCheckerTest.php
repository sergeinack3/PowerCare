<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Checkers;

use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\WeakPasswordBadge;
use Ox\Core\Auth\Exception\CredentialsCheckException;
use Ox\Core\Auth\User;
use Ox\Core\Security\Crypt\Hash;
use Ox\Core\Security\Crypt\Hasher;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractCredentialsCheckerTest extends OxUnitTestCase
{
    abstract public function getClassName(): string;

    /**
     * @dataProvider validPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testValidCredentials(string $password, UserInterface $user): void
    {
        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $this->assertTrue($checker->check($password, $user));
    }

    /**
     * @dataProvider invalidPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testInvalidCredentials(string $password, UserInterface $user): void
    {
        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $this->assertFalse($checker->check($password, $user));
    }

    /**
     * @dataProvider invalidUsersProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testCheckerOnlyAcceptsUser(string $password, UserInterface $user): void
    {
        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $this->assertFalse($checker->check($password, $user));
    }

    /**
     * @dataProvider weakPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testPasswordBadgeIsMarkedAsWeak(string $password, UserInterface $user): void
    {
        $weak_password_badge = new WeakPasswordBadge();
        $this->assertFalse($weak_password_badge->isEnabled());

        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $checker->setWeakPasswordBadge($weak_password_badge);

        $checker->check($password, $user);

        $this->assertTrue($weak_password_badge->isEnabled());
    }

    /**
     * @dataProvider nonWeakPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testPasswordBadgeIsNotMarkedAsWeak(string $password, UserInterface $user): void
    {
        $weak_password_badge = new WeakPasswordBadge();
        $this->assertFalse($weak_password_badge->isEnabled());

        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $checker->setWeakPasswordBadge($weak_password_badge);

        $checker->check($password, $user);

        $this->assertFalse($weak_password_badge->isEnabled());
    }

    /**
     * @dataProvider validPasswordProvider
     * @dataProvider invalidPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     */
    public function testLogAuthIsSet(string $password, UserInterface $user): void
    {
        $log_auth_badge = new LogAuthBadge('TEST');
        $this->assertEquals('TEST', $log_auth_badge->getMethod());

        $class   = $this->getClassName();
        $checker = new $class($this->getHasher());

        $checker->setLogAuthBadge($log_auth_badge);

        $checker->check($password, $user);

        $this->assertEquals($checker->getMethod(), $log_auth_badge->getMethod());
    }

    public function validPasswordProvider(): array
    {
        return $this->getValidProviderData(User::class, false);
    }

    private function getValidProviderData(string $user_class, bool $weak_password): array
    {
        return [
            'valid password'  => ['password', $this->mockUser($user_class, 'password', 'salt', $weak_password)],
            'null salt in db' => ['password', $this->mockUser($user_class, 'password', null, $weak_password)],
        ];
    }

    public function invalidPasswordProvider(): array
    {
        return $this->getInvalidProviderData(User::class);
    }

    private function getInvalidProviderData(string $user_class): array
    {
        return [
            'invalid password'        => ['invalid', $this->mockUser($user_class, 'password', 'salt', false)],
            'null password in db'     => ['invalid', $this->mockUser($user_class, null, 'salt', false)],
            'null salt and pwd in db' => ['invalid', $this->mockUser($user_class, null, null, false)],
        ];
    }

    public function invalidUsersProvider(): array
    {
        return $this->getValidProviderData(UserInterface::class, false)
            + $this->getInvalidProviderData(UserInterface::class);
    }

    public function weakPasswordProvider(): array
    {
        return $this->getValidProviderData(User::class, true);
    }

    public function nonWeakPasswordProvider(): array
    {
        return $this->getValidProviderData(User::class, false);
    }

    /**
     * @param string $user_class
     * @param        $user_password
     * @param        $user_salt
     * @param bool   $check_password_weakness
     *
     * @return User
     */
    private function mockUser(
        string $user_class,
               $user_password,
               $user_salt,
        bool   $check_password_weakness
    ): UserInterface {
        $ox_user = $this->getMockBuilder(CUser::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(['checkPasswordWeakness'])
                        ->getMock();

        $ox_user->expects($this->once())->method('checkPasswordWeakness')->willReturn($check_password_weakness);
        $ox_user->user_password = $this->getHasher()->hash(Hash::SHA256(), $user_salt . $user_password);
        $ox_user->user_salt     = $user_salt;

        if ($user_class === UserInterface::class) {
            $user = $this->getMockBuilder($user_class)
                         ->disableOriginalConstructor()
                         ->getMock();
        } else {
            $user = $this->getMockBuilder($user_class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['getOxUser'])
                         ->getMock();

            $user->expects($this->once())->method('getOxUser')->willReturn($ox_user);
        }

        return $user;
    }

    private function getHasher(): Hasher
    {
        static $hasher;

        if ($hasher === null) {
            $hasher = new Hasher();
        }

        return $hasher;
    }
}
