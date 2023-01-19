<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Checkers;

use Exception;
use Ox\Core\Auth\Checkers\UserChecker;
use Ox\Core\Auth\Exception\CouldNotValidatePreAuthentication;
use Ox\Core\Auth\User;
use Ox\Core\CMbDT;
use Ox\Core\Config\Conf;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends OxUnitTestCase
{
    /**
     * @dataProvider usersProvider
     *
     * @param UserInterface  $user
     * @param Exception|null $e
     *
     * @return void
     * @throws Exception
     */
    public function testCheckerOnlyAcceptsUser(UserInterface $user, bool $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject(CouldNotValidatePreAuthentication::userIsNotSupported());
        }

        $checker = new UserChecker($this->mockConf(false, ''));
        $checker->checkPreAuth($user);
    }

    /**
     * @dataProvider forbiddenUsersProvider
     *
     * @param UserInterface    $user
     * @param Conf|UserChecker $conf
     * @param Exception        $e
     *
     * @return void
     * @throws Exception
     */
    public function testUserIsNotAllowed(UserInterface $user, $conf, Exception $e): void
    {
        $this->expectExceptionObject($e);

        $checker = $conf instanceof Conf ? new UserChecker($conf) : $conf;
        $checker->checkPreAuth($user);
    }

    /**
     * @dataProvider allowedUsersProvider
     *
     * @param UserInterface    $user
     * @param Conf|UserChecker $conf
     * @param Exception        $e
     *
     * @return void
     * @throws Exception
     */
    public function testUserIsAllowed(UserInterface $user, $conf): void
    {
        $checker = $conf instanceof Conf ? new UserChecker($conf) : $conf;
        $checker->checkPreAuth($user);
    }

    public function usersProvider(): array
    {
        return [
            'user accepted'            => [$this->mockUser(User::class, false, false, false, false), false],
            'user interface forbidden' => [$this->mockUser(UserInterface::class, false, false, false, false), true],
        ];
    }

    public function forbiddenUsersProvider(): array
    {
        return [
            'template'                                  => [
                $this->mockUser(User::class, true, false, false, false),
                new Conf(),
                CouldNotValidatePreAuthentication::userIsATemplate(),
            ],
            'secondary'                                 => [
                $this->mockUser(User::class, false, true, false, false),
                new Conf(),
                CouldNotValidatePreAuthentication::userIsSecondary(),
            ],
            'locked'                                    => [
                $this->mockUser(User::class, false, false, true, false),
                new Conf(),
                CouldNotValidatePreAuthentication::userIsLocked(),
            ],
            'system is offline and user is not admin'   => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockConf(true, ''),
                CouldNotValidatePreAuthentication::systemIsOfflineForNonAdmins(),
            ],
            'mediuser is deactivated'                   => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker($this->mockConf(false, ''), true, $this->mockMediuser(false, false), false, ''),
                CouldNotValidatePreAuthentication::userIsDeactivated(),
            ],
            'mediuser has expired'                      => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker(
                    $this->mockConf(false, ''),
                    true,
                    $this->mockMediuser(true, false, CMbDT::date('-2 day'), CMbDT::date('-1 day')),
                    false,
                    ''
                ),
                CouldNotValidatePreAuthentication::userAccountHasExpired(),
            ],
            'mediuser (non-admin) has no remote access' => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker(
                    $this->mockConf(false, ''),
                    true,
                    $this->mockMediuser(true, false),
                    false,
                    ''
                ),
                CouldNotValidatePreAuthentication::userHasNoRemoteAccess(),
            ],
            'mediuser has no access from location'      => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker(
                    $this->mockConf(false, '127.0.0.1'),
                    true,
                    $this->mockMediuser(true, false),
                    true,
                    '127.0.0.2'
                ),
                CouldNotValidatePreAuthentication::userHasNoAccessFromThisLocation(),
            ],
        ];
    }

    public function allowedUsersProvider(): array
    {
        return [
            'normal user'                               => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockConf(false, ''),
            ],
            'admin user'                                => [
                $this->mockUser(User::class, false, false, false, true),
                $this->mockConf(false, ''),
            ],
            'system is offline but user is admin'       => [
                $this->mockUser(User::class, false, false, false, true),
                $this->mockConf(true, ''),
            ],
            'with mediuser'                             => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker($this->mockConf(false, ''), true, $this->mockMediuser(true, false), true, ''),
            ],
            'without mediuser module when not intranet' => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker($this->mockConf(false, ''), false, null, false, ''),
            ],
            'without mediuser when not intranet'        => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker($this->mockConf(false, ''), true, null, false, ''),
            ],
            'with remote access allowed'                => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker(
                    $this->mockConf(false, ''),
                    true,
                    $this->mockMediuser(true, true),
                    false,
                    ''
                ),
            ],
            'without remote access but is admin'        => [
                $this->mockUser(User::class, false, false, false, true),
                $this->mockChecker(
                    $this->mockConf(false, ''),
                    true,
                    $this->mockMediuser(true, false),
                    false,
                    ''
                ),
            ],
            'access from location'                      => [
                $this->mockUser(User::class, false, false, false, false),
                $this->mockChecker(
                    $this->mockConf(false, '127.0.0.1'),
                    true,
                    $this->mockMediuser(true, false),
                    true,
                    '127.0.0.1'
                ),
            ],
        ];
    }

    private function mockChecker(
        Conf        $conf,
        bool        $mediuser_module,
        ?CMediusers $mediusers,
        bool        $intranet,
        string      $ip
    ): UserChecker {
        $user_checker = $this->getMockBuilder(UserChecker::class)
                             ->setConstructorArgs([$conf])
                             ->onlyMethods(['isMediusersModuleActive', 'loadMediuser', 'isIntranet', 'getIp'])
                             ->getMock();

        $user_checker->expects($this->any())->method('isMediusersModuleActive')->willReturn($mediuser_module);
        $user_checker->expects($this->any())->method('loadMediuser')->willReturn($mediusers);
        $user_checker->expects($this->any())->method('isIntranet')->willReturn($intranet);
        $user_checker->expects($this->any())->method('getIp')->willReturn($ip);

        return $user_checker;
    }

    private function mockMediuser(
        bool   $actif,
        bool   $remote,
        string $deb_activite = null,
        string $fin_activite = null
    ): CMediusers {
        $user = $this->getMockBuilder(CMediusers::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $user->_id   = 'test';
        $user->actif = $actif;
        // Remote=1 means NO remote is allowed...
        $user->remote       = $remote ? 0 : 1;
        $user->deb_activite = $deb_activite;
        $user->fin_activite = $fin_activite;

        $user->expects($this->any())->method('loadRefFunction')->willReturnCallback(function () {
            $function           = new CFunctions();
            $function->group_id = 1;

            return $function;
        });

        return $user;
    }

    private function mockConf(bool $offline, string $network_whitelist): Conf
    {
        $conf = $this->getMockBuilder(Conf::class)->getMock();

        $conf->expects($this->any())->method('get')->willReturn($offline);
        $conf->expects($this->any())->method('getForGroupId')->willReturn($network_whitelist);

        return $conf;
    }

    private function mockUser(
        string $user_class,
        bool   $template,
        bool   $secondary,
        bool   $locked,
        bool   $admin
    ): UserInterface {
        $ox_user = $this->getMockBuilder(CUser::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $ox_user->user_username = 'username';
        $ox_user->template      = $template;

        $ox_user->expects($this->any())->method('isSecondary')->willReturn($secondary);
        $ox_user->expects($this->any())->method('isLocked')->willReturn($locked);
        $ox_user->expects($this->any())->method('isAdmin')->willReturn($admin);

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
}
