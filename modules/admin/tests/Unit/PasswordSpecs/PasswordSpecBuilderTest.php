<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Tests\Unit\PasswordSpecs;

use Exception;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\PasswordSpecs\PasswordSpecBuilder;
use Ox\Mediboard\Admin\PasswordSpecs\PasswordSpecConfiguration;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class PasswordSpecBuilderTest extends OxUnitTestCase
{
    public function weakProvider(): array
    {
        $configuration_weak  = $this->getWeakPasswordConfiguration();
        $configuration_local = $this->getStrongPasswordConfiguration(false);

        return [
            'weak configuration / non admin user'                 => [
                $this->buildUser(false, false, false, false),
                $configuration_weak,
            ],
            'weak configuration / non admin user without remote'  => [
                $this->buildUser(true, false, false, false),
                $configuration_weak,
            ],
            'weak configuration / non admin user with remote'     => [
                $this->buildUser(true, false, true, false),
                $configuration_weak,
            ],
            'weak configuration / admin user'                     => [
                $this->buildUser(false, true, false, false),
                $configuration_weak,
            ],
            'weak configuration / admin user without remote'      => [
                $this->buildUser(true, true, false, false),
                $configuration_weak,
            ],
            'weak configuration / admin user with remote'         => [
                $this->buildUser(true, true, true, false),
                $configuration_weak,
            ],
            'local configuration / non admin user'                => [
                $this->buildUser(false, false, false, false),
                $configuration_local,
            ],
            'local configuration / non admin user without remote' => [
                $this->buildUser(true, false, false, false),
                $configuration_local,
            ],
            'local configuration / admin user'                    => [
                $this->buildUser(false, true, false, false),
                $configuration_local,
            ],
            'local configuration / admin user without remote'     => [
                $this->buildUser(true, true, false, false),
                $configuration_local,
            ],
        ];
    }

    public function strongProvider(): array
    {
        $configuration_local = $this->getStrongPasswordConfiguration(false);
        $configuration_all   = $this->getStrongPasswordConfiguration(true);

        return [
            'local configuration / non admin user with remote'  => [
                $this->buildUser(true, false, true, false),
                $configuration_local,
            ],
            'local configuration / admin user with remote'      => [
                $this->buildUser(true, true, true, false),
                $configuration_local,
            ],
            'all configuration / non admin user without remote' => [
                $this->buildUser(true, false, false, false),
                $configuration_all,
            ],
            'all configuration / non admin user with remote'    => [
                $this->buildUser(true, false, true, false),
                $configuration_all,
            ],
            'all configuration / admin user without remote'     => [
                $this->buildUser(true, true, false, false),
                $configuration_all,
            ],
            'all configuration / admin user with remote'        => [
                $this->buildUser(true, true, true, false),
                $configuration_all,
            ],
        ];
    }

    public function adminProvider(): array
    {
        $configuration_admin_local = $this->getAdminPasswordConfiguration(false);
        $configuration_admin_all   = $this->getAdminPasswordConfiguration(true);

        return [
            'local configuration / admin user with remote'    => [
                $this->buildUser(true, true, true, false),
                $configuration_admin_local,
            ],
            'local configuration / admin user without remote' => [
                $this->buildUser(true, true, false, false),
                $configuration_admin_local,
            ],
            'all configuration / admin user with remote'      => [
                $this->buildUser(true, true, true, false),
                $configuration_admin_all,
            ],
            'all configuration / admin user without remote'   => [
                $this->buildUser(true, true, false, false),
                $configuration_admin_all,
            ],
        ];
    }

    public function userLDAPProvider(): array
    {
        $configuration_weak        = $this->getWeakPasswordConfiguration();
        $configuration_local       = $this->getStrongPasswordConfiguration(false);
        $configuration_strong_all  = $this->getStrongPasswordConfiguration(true);
        $configuration_admin_local = $this->getAdminPasswordConfiguration(false);
        $configuration_admin_all   = $this->getAdminPasswordConfiguration(true);

        return [
            'weak configuration / LDAP non admin user'                       => [
                $this->buildUser(false, false, false, true),
                $configuration_weak,
            ],
            'weak configuration / LDAP non admin user without remote'        => [
                $this->buildUser(true, false, false, true),
                $configuration_weak,
            ],
            'weak configuration / LDAP non admin user with remote'           => [
                $this->buildUser(true, false, true, true),
                $configuration_weak,
            ],
            'weak configuration / LDAP admin user'                           => [
                $this->buildUser(false, true, false, true),
                $configuration_weak,
            ],
            'weak configuration / LDAP admin user without remote'            => [
                $this->buildUser(true, true, false, true),
                $configuration_weak,
            ],
            'weak configuration / LDAP admin user with remote'               => [
                $this->buildUser(true, true, true, true),
                $configuration_weak,
            ],
            'local configuration / LDAP non admin user'                      => [
                $this->buildUser(false, false, false, true),
                $configuration_local,
            ],
            'local configuration / LDAP non admin user without remote'       => [
                $this->buildUser(true, false, false, true),
                $configuration_local,
            ],
            'local configuration / LDAP non admin user with remote'          => [
                $this->buildUser(true, false, true, true),
                $configuration_local,
            ],
            'local configuration / LDAP admin user'                          => [
                $this->buildUser(false, true, false, true),
                $configuration_local,
            ],
            'local configuration / LDAP admin user without remote'           => [
                $this->buildUser(true, true, false, true),
                $configuration_local,
            ],
            'local configuration / LDAP admin user with remote'              => [
                $this->buildUser(true, true, true, true),
                $configuration_local,
            ],
            'strong configuration / LDAP non admin user'                     => [
                $this->buildUser(false, false, false, true),
                $configuration_strong_all,
            ],
            'strong configuration / LDAP non admin user without remote'      => [
                $this->buildUser(true, false, false, true),
                $configuration_strong_all,
            ],
            'strong configuration / LDAP non admin user with remote'         => [
                $this->buildUser(true, false, true, true),
                $configuration_strong_all,
            ],
            'strong configuration / LDAP admin user'                         => [
                $this->buildUser(false, true, false, true),
                $configuration_strong_all,
            ],
            'strong configuration / LDAP admin user without remote'          => [
                $this->buildUser(true, true, false, true),
                $configuration_strong_all,
            ],
            'strong configuration / LDAP admin user with remote'             => [
                $this->buildUser(true, true, true, true),
                $configuration_strong_all,
            ],
            'admin local configuration / LDAP non admin user'                => [
                $this->buildUser(false, false, false, true),
                $configuration_admin_local,
            ],
            'admin local configuration / LDAP non admin user without remote' => [
                $this->buildUser(true, false, false, true),
                $configuration_admin_local,
            ],
            'admin local configuration / LDAP non admin user with remote'    => [
                $this->buildUser(true, false, true, true),
                $configuration_admin_local,
            ],
            'admin local configuration / LDAP admin user'                    => [
                $this->buildUser(false, true, false, true),
                $configuration_admin_local,
            ],
            'admin local configuration / LDAP admin user without remote'     => [
                $this->buildUser(true, true, false, true),
                $configuration_admin_local,
            ],
            'admin local configuration / LDAP admin user with remote'        => [
                $this->buildUser(true, true, true, true),
                $configuration_admin_local,
            ],
            'admin all configuration / LDAP non admin user'                  => [
                $this->buildUser(false, false, false, true),
                $configuration_admin_all,
            ],
            'admin all configuration / LDAP non admin user without remote'   => [
                $this->buildUser(true, false, false, true),
                $configuration_admin_all,
            ],
            'admin all configuration / LDAP non admin user with remote'      => [
                $this->buildUser(true, false, true, true),
                $configuration_admin_all,
            ],
            'admin all configuration / LDAP admin user'                      => [
                $this->buildUser(false, true, false, true),
                $configuration_admin_all,
            ],
            'admin all configuration / LDAP admin user without remote'       => [
                $this->buildUser(true, true, false, true),
                $configuration_admin_all,
            ],
            'admin all configuration / LDAP admin user with remote'          => [
                $this->buildUser(true, true, true, true),
                $configuration_admin_all,
            ],
        ];
    }

    /**
     * @dataProvider weakProvider
     *
     * @param CUser|CMediusers          $user
     * @param PasswordSpecConfiguration $configuration
     *
     * @throws Exception
     */
    public function testUserHasWeakPasswordSpecification(CUser $user, PasswordSpecConfiguration $configuration): void
    {
        $factory = new PasswordSpecBuilder($user, $configuration);
        $spec    = $factory->build();

        $this->assertTrue($spec->isWeak());
    }

    /**
     * @dataProvider strongProvider
     *
     * @param CUser|CMediusers          $user
     * @param PasswordSpecConfiguration $configuration
     *
     * @throws Exception
     */
    public function testUserHasStrongPasswordSpecification(CUser $user, PasswordSpecConfiguration $configuration): void
    {
        $factory = new PasswordSpecBuilder($user, $configuration);
        $spec    = $factory->build();

        $this->assertTrue($spec->isStrong());
    }

    /**
     * @dataProvider adminProvider
     *
     * @param CUser|CMediusers          $user
     * @param PasswordSpecConfiguration $configuration
     *
     * @throws Exception
     */
    public function testUserHasAdminPasswordSpecification(CUser $user, PasswordSpecConfiguration $configuration): void
    {
        $factory = new PasswordSpecBuilder($user, $configuration);
        $spec    = $factory->build();

        $this->assertTrue($spec->isAdmin());
    }

    /**
     * @dataProvider userLDAPProvider
     *
     * @param CUser|CMediusers          $user
     * @param PasswordSpecConfiguration $configuration
     *
     * @throws Exception
     */
    public function testUserHasLDAPPasswordSpecification(CUser $user, PasswordSpecConfiguration $configuration): void
    {
        $factory = new PasswordSpecBuilder($user, $configuration);
        $spec    = $factory->build();

        $this->assertTrue($spec->isLDAP());
    }

    private function getWeakPasswordConfiguration(): PasswordSpecConfiguration
    {
        $configuration                    = new PasswordSpecConfiguration();
        $configuration['strong_password'] = false;

        return $configuration;
    }

    private function getStrongPasswordConfiguration(bool $apply_all_users): PasswordSpecConfiguration
    {
        $configuration                      = new PasswordSpecConfiguration();
        $configuration['strong_password']   = true;
        $configuration['apply_all_users']   = $apply_all_users;
        $configuration['strong_min_length'] = 8;

        return $configuration;
    }

    private function getAdminPasswordConfiguration(bool $apply_all_users): PasswordSpecConfiguration
    {
        $configuration                     = new PasswordSpecConfiguration();
        $configuration['strong_password']  = true;
        $configuration['apply_all_users']  = $apply_all_users;
        $configuration['admin_specific']   = true;
        $configuration['admin_min_length'] = 14;

        return $configuration;
    }

    private function buildUser(bool $with_mediuser, bool $admin, bool $remote, bool $ldap): CUser
    {
        $user = $this->getMockBuilder(CUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLDAPLinked'])
            ->getMock();

        $user->expects($this->atLeast(1))->method('isLDAPLinked')->willReturn($ldap);

        $user->user_type = '14';

        if ($admin) {
            $user->user_type = '1';
        }

        if ($with_mediuser) {
            $user->_ref_mediuser = $this->buildMediuser(false, $admin, $remote, $ldap);
        }

        return $user;
    }

    private function buildMediuser(bool $with_user, bool $admin, bool $remote, bool $ldap): CMediusers
    {
        $user = $this->getMockBuilder(CMediusers::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLDAPLinked'])
            ->getMock();

        $user->expects($this->atLeast(1))->method('isLDAPLinked')->willReturn($ldap);

        if ($with_user) {
            $user->_ref_user = $this->buildUser(false, $admin, false, $ldap);
        }

        // Remote is REVERTED
        $user->remote = !$remote;

        return $user;
    }
}
