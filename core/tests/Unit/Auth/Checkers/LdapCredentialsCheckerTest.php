<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Checkers;

use Exception;
use Ox\Core\Auth\Badges\IncrementLoginAttemptsBadge;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Checkers\LdapCredentialsChecker;
use Ox\Core\Auth\Exception\CredentialsCheckException;
use Ox\Core\Auth\User;
use Ox\Core\CMbException;
use Ox\Core\Config\Conf;
use Ox\Core\Security\Crypt\Hasher;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CLDAPNoSourceAvailableException;
use Ox\Mediboard\Admin\CMbInvalidCredentialsException;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapCredentialsCheckerTest extends OxUnitTestCase
{
    /**
     * @dataProvider validPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testValidCredentials(UserInterface $user): void
    {
        $checker = new LdapCredentialsChecker($this->mockConf(true), $this->mockLDAP(true));

        $this->assertTrue($checker->check('password', $user));
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
    public function testInvalidCredentials(UserInterface $user): void
    {
        $checker = new LdapCredentialsChecker($this->mockConf(true), $this->mockLDAP(false));

        $this->assertFalse($checker->check('password', $user));
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
    public function testCheckerOnlyAcceptsUser(UserInterface $user): void
    {
        $checker = new LdapCredentialsChecker($this->mockConf(true), $this->mockLDAP(true));

        $this->assertFalse($checker->check('password', $user));
    }

    public function testLdapIsNotQueriedWhenDisabled(): void
    {
        $ldap_provider = $this->getMockBuilder(CLDAP::class)
                              ->onlyMethods(['logUser'])
                              ->getMock();

        $ldap_provider->expects($this->never())->method('logUser');

        $checker = new LdapCredentialsChecker($this->mockConf(false), $ldap_provider);

        $checker->check('password', $this->mockUser(User::class, 'username', 'ldap_uid'));
    }

    public function testLdapIsNotQueriedWhenUserIsNotRelated(): void
    {
        $ldap_provider = $this->getMockBuilder(CLDAP::class)
                              ->onlyMethods(['logUser'])
                              ->getMock();

        $ldap_provider->expects($this->never())->method('logUser');

        $checker = new LdapCredentialsChecker($this->mockConf(true), $ldap_provider);

        $checker->check('password', $this->mockUser(User::class, 'username', null));
    }

    /**
     * @dataProvider incrementProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testIncrementBadgeIsDisabled(UserInterface $user): void
    {
        $this->expectException(CredentialsCheckException::class);

        $increment_badge = new IncrementLoginAttemptsBadge();
        $this->assertTrue($increment_badge->isEnabled());

        $checker = new LdapCredentialsChecker(
            $this->mockConf(true),
            $this->mockLDAP(false, new CMbInvalidCredentialsException(''))
        );

        $checker->setIncrementLogAttemptsBadge($increment_badge);

        $this->assertFalse($checker->check('password', $user));
        $this->assertFalse($increment_badge->isEnabled());
    }

    /**
     * @dataProvider nonIncrementProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testIncrementBadgeIsNotDisabled(UserInterface $user, Exception $e): void
    {
        $increment_badge = new IncrementLoginAttemptsBadge();
        $this->assertTrue($increment_badge->isEnabled());

        $checker = new LdapCredentialsChecker(
            $this->mockConf(true),
            $this->mockLDAP(false, $e)
        );

        $checker->setIncrementLogAttemptsBadge($increment_badge);

        $this->assertFalse($checker->check('password', $user));
        $this->assertTrue($increment_badge->isEnabled());
    }

    /**
     * @dataProvider validPasswordProvider
     * @dataProvider invalidPasswordProvider
     *
     * @param string        $password
     * @param UserInterface $user
     *
     * @return void
     * @throws CredentialsCheckException
     */
    public function testLogAuthIsSet(UserInterface $user): void
    {
        $log_auth_badge = new LogAuthBadge('TEST');
        $this->assertEquals('TEST', $log_auth_badge->getMethod());

        $checker = new LdapCredentialsChecker($this->mockConf(true), $this->mockLDAP(true));

        $checker->setLogAuthBadge($log_auth_badge);

        $checker->check('password', $user);

        $this->assertEquals($checker->getMethod(), $log_auth_badge->getMethod());
    }

    public function validPasswordProvider(): array
    {
        return $this->getValidProviderData(User::class, 'username', 'ldap_uid');
    }

    private function getValidProviderData(string $user_class, ?string $username, ?string $ldap_uid): array
    {
        return [
            'bound' => [$this->mockUser($user_class, $username, $ldap_uid)],
        ];
    }

    public function invalidPasswordProvider(): array
    {
        return $this->getInvalidProviderData(User::class, 'username', 'ldap_uid');
    }

    private function getInvalidProviderData(string $user_class, ?string $username, ?string $ldap_uid): array
    {
        return [
            'not bound' => [$this->mockUser($user_class, $username, $ldap_uid)],
        ];
    }

    public function invalidUsersProvider(): array
    {
        return $this->getValidProviderData(UserInterface::class, 'username', 'ldap_uid')
            + $this->getInvalidProviderData(UserInterface::class, 'username', 'ldap_uid');
    }

    public function incrementProvider(): array
    {
        return $this->getValidProviderData(User::class, 'username', 'ldap_uid');
    }

    public function nonIncrementProvider(): array
    {
        return [
            'No pool connection'   => [
                $this->mockUser(User::class, 'username', 'ldap_uid'),
                new CLDAPNoSourceAvailableException(''),
            ],
            'Unexpected exception' => [$this->mockUser(User::class, 'username', 'ldap_uid'), new CMbException('')],
        ];
    }

    /**
     * @param string      $user_class
     * @param string|null $user_name
     * @param string|null $ldap_uid
     *
     * @return UserInterface
     */
    private function mockUser(
        string  $user_class,
        ?string $user_name,
        ?string $ldap_uid
    ): UserInterface {
        $ox_user = $this->getMockBuilder(CUser::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(['getLdapUid'])
                        ->getMock();

        $ox_user->expects($this->any())->method('getLdapUid')->willReturn($ldap_uid);

        $ox_user->user_username = $user_name;

        if ($user_class === UserInterface::class) {
            $user = $this->getMockBuilder($user_class)
                         ->disableOriginalConstructor()
                         ->getMock();
        } else {
            $user = $this->getMockBuilder($user_class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['getOxUser'])
                         ->getMock();

            $user->expects($this->any())->method('getOxUser')->willReturn($ox_user);
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

    private function mockLDAP(bool $bound, Exception $e = null): CLDAP
    {
        $ldap_provider = $this->getMockBuilder(CLDAP::class)
                              ->onlyMethods(['logUser'])
                              ->getMock();

        if ($e === null) {
            $user         = new CUser();
            $user->_bound = $bound;

            $ldap_provider->expects($this->any())->method('logUser')->willReturn($user);
        } else {
            $ldap_provider->expects($this->any())->method('logUser')->willThrowException($e);
        }

        return $ldap_provider;
    }

    private function mockConf(bool $ldap_enabled): Conf
    {
        $conf = $this->getMockBuilder(Conf::class)->getMock();

        $conf->expects($this->any())->method('get')->willReturn($ldap_enabled);

        return $conf;
    }
}
