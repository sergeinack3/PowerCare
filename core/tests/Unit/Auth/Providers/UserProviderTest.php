<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Providers;

use Ox\Core\Auth\Providers\UserProvider;
use Ox\Core\Auth\User;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends OxUnitTestCase
{
    private const VALID_USERNAME   = CUser::USER_PHPUNIT;
    private const INVALID_USERNAME = 'highly_unexpected_user_to_exist';

    /**
     * @dataProvider userClassesProvider
     *
     * @param string $class
     * @param bool   $expected
     *
     * @return void
     */
    public function testSupportsOnlyUser(string $class, bool $expected): void
    {
        $provider = new UserProvider();

        $this->assertEquals($expected, $provider->supportsClass($class));
    }

    public function testLoadByUsername(): void
    {
        $provider = new UserProvider();

        $user = $provider->loadUserByUsername(self::VALID_USERNAME);

        $this->assertUserIsValid($provider, $user);
    }

    public function testLoadUserByIdentifier(): void
    {
        $provider = new UserProvider();

        $user = $provider->loadUserByIdentifier(self::VALID_USERNAME);

        $this->assertUserIsValid($provider, $user);
    }

    /**
     * @dataProvider notFoundUserProvider
     * @return void
     */
    public function testLoadUserByUsernameWhenNotFoundUserThrowsAnException(string $username): void
    {
        $this->expectException(UserNotFoundException::class);

        $provider = new UserProvider();
        $provider->loadUserByUsername($username);
    }

    /**
     * @dataProvider notFoundUserProvider
     * @return void
     */
    public function testLoadUserByIdentifierWhenNotFoundUserThrowsAnException(string $username): void
    {
        $this->expectException(UserNotFoundException::class);

        $provider = new UserProvider();
        $provider->loadUserByIdentifier($username);
    }

    public function testRefreshValidUser(): void
    {
        $provider = new UserProvider();

        $valid_user = new User($this->getValidCUser());

        $refreshed_user = $provider->refreshUser($valid_user);

        $this->assertUserIsValid($provider, $refreshed_user);
        $this->assertObjectEquals($valid_user, $refreshed_user);
    }

    /**
     * @dataProvider invalidUsersProvider
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function testRefreshInvalidUserThrowsAnException(UserInterface $user): void
    {
        $this->expectException(UnsupportedUserException::class);

        $provider = new UserProvider();
        $provider->refreshUser($user);
    }

    public function testLoadValidOxUser(): void
    {
        $provider = new UserProvider();

        $ox_user    = $this->getValidCUser();
        $valid_user = new User($ox_user);

        $actual_user = $provider->loadOxUser($valid_user);

        $this->assertEquals($ox_user->_id, $actual_user->_id);
    }

    /**
     * @dataProvider invalidUsersProvider
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function testLoadInvalidOxUserThrowsAnException(UserInterface $user): void
    {
        $this->expectException(UnsupportedUserException::class);

        $provider = new UserProvider();
        $provider->loadOxUser($user);
    }

    public function testLoadOxUserById(): void
    {
        $ox_user = $this->getValidCUser();

        $provider = new UserProvider();

        $user = $provider->loadOxUserById($ox_user->_id);

        $this->assertEquals($ox_user->_id, $user->_id);

        $this->expectException(CMbModelNotFoundException::class);
        $provider->loadOxUserById('id that does not exist');
    }

    /**
     * @param UserProvider $provider
     * @param mixed        $user
     *
     * @return void
     */
    private function assertUserIsValid(UserProvider $provider, $user): void
    {
        $this->assertIsObject($user);
        $this->assertTrue($provider->supportsClass(get_class($user)));

        $this->assertEquals(self::VALID_USERNAME, $user->getUserIdentifier());
        $this->assertNull($user->getPassword());

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_API_USER', $user->getRoles());
    }

    private function getValidCUser(): CUser
    {
        $user                = new CUser();
        $user->user_username = self::VALID_USERNAME;
        $user->loadMatchingObjectEsc();

        return $user;
    }

    public function userClassesProvider(): array
    {
        return [
            'User Interface - not supported' => [UserInterface::class, false],
            'User - supported'               => [User::class, true],
            'CUser - not supported'          => [CUser::class, false],
            'Empty class - not supported'    => ['', false],
        ];
    }

    public function invalidUsersProvider(): array
    {
        return [
            'User Interface' => [$this->getMockBuilder(UserInterface::class)->getMock()],
        ];
    }

    public function notFoundUserProvider(): array
    {
        return [
            'Not found username' => [self::INVALID_USERNAME],
            'Empty username'     => [self::INVALID_USERNAME],
        ];
    }
}
