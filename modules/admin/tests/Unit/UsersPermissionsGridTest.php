<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Tests\Unit;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersModulesPermissionsFixtures;
use Ox\Mediboard\Admin\UsersPermissionsGrid;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class UsersPermissionsGridTest extends OxUnitTestCase
{
    private CMediusers $user;

    private CGroups $group;

    private CModule $module;

    private CUser $profile;

    /**
     * @throws TestsException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = $this->getObjectFromFixturesReference(
            CMediusers::class,
            UsersModulesPermissionsFixtures::REF_USER_PERMISSIONS
        );
        /** @var CMediusers user */
        $this->group   = $this->user->loadRefFunction()->loadRefGroup();
        $this->profile = $this->user->loadRefProfile();
        $this->module  = CModule::getInstalled("system");
    }

    /**
     * @throws Exception
     */
    public function testGetListFunctionContainsUsersFunction(): void
    {
        $user_permission_grid = new UsersPermissionsGrid(
            $this->group,
            true,
            false,
            [],
            [$this->user->_id => $this->user->_id],
            []
        );

        $this->assertArrayHasKey($this->user->loadRefFunction()->_id, $user_permission_grid->getListFunctions());
    }

    /**
     * @throws Exception
     */
    public function testGetMatrixContainsUserContainsUsersPermissionsForModule(): void
    {
        $user_permission_grid = new UsersPermissionsGrid(
            $this->group,
            true,
            false,
            [$this->profile->_id => $this->profile->_id],
            [$this->user->_id => $this->user->_id],
            [$this->module->mod_name => $this->module]
        );

        $matrix = $user_permission_grid->getMatrix();

        $expected = [
            $this->user->_id => [
                $this->module->_id => [
                    "text"     => "CPermModule.permission.0/CPermModule.view.0",
                    "type"     => "spécifique",
                    "permIcon" => "empty",
                    "viewIcon" => "empty",
                ],
            ],
        ];
        $this->assertEquals($expected, $matrix);
    }

    /**
     * @throws Exception
     */
    public function testGetMatrixProfileContainsProfilePermissionsForModule(): void
    {
        $user_permission_grid = new UsersPermissionsGrid(
            $this->group,
            false,
            true,
            [$this->profile->_id => $this->profile->_id],
            [],
            [$this->module->mod_name => $this->module]
        );

        $matrix = $user_permission_grid->getMatrixProfiles();

        $expected = [
            "text"     => "CPermModule.permission.0/CPermModule.view.0",
            "type"     => "spécifique",
            "permIcon" => "empty",
            "viewIcon" => "empty",
        ];
        foreach ($user_permission_grid->getProfiles() as $profile) {
            $this->assertArrayHasKey($profile->_id, $matrix);
            $this->assertArrayHasKey($this->module->_id, $matrix[$profile->_id]);
            $this->assertEquals($expected, $matrix[$profile->_id][$this->module->_id]);
        }
    }
}
