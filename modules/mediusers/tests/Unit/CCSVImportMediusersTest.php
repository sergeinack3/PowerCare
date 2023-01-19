<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Tests\Unit;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CCSVImportMediusers;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class CCSVImportMediusersTest extends OxUnitTestCase
{
    public function testGetUserEmpty(): void
    {
        $mock = $this->getMockWithoutConstruct();

        $this->assertEquals(new CUser(), $this->invokePrivateMethod($mock, 'getUser', []));
    }

    public function testGetUser(): void
    {
        $mock = $this->getMockWithoutConstruct();

        $username = uniqid();
        /** @var CUser $user */
        $user = $this->invokePrivateMethod($mock, 'getUser', ['nom' => 'Lorem', 'username' => $username, 'type' => 14]);

        $this->assertEquals(14, $user->user_type);
        $this->assertEquals($username, $user->user_username);
        $this->assertNull($user->_id);
    }

    public function testGetUserExists(): void
    {
        $mock = $this->getMockWithoutConstruct();

        /** @var CUser $existing_user */
        $existing_user = CUser::get();
        /** @var CUser $user */
        $user          = $this->invokePrivateMethod(
            $mock,
            'getUser',
            [
                'nom'      => $existing_user->user_last_name,
                'username' => $existing_user->user_username,
            ]
        );

        $this->assertEquals($existing_user, $user);
    }

    public function testGetFunctionNoText(): void
    {
        $mock = $this->getMockWithoutConstruct();
        $this->assertNull($this->invokePrivateMethod($mock, 'getFunction', null, '14'));
    }

    public function testGetFunctionExists(): void
    {
        /** @var CFunctions $function */
        $function           = CFunctions::getSampleObject();
        $function->group_id = CGroups::loadCurrent()->_id;
        $this->storeOrFailed($function);

        $mock = $this->getMockWithoutConstruct();

        $this->assertEquals(
            $function->_id,
            ($this->invokePrivateMethod($mock, 'getFunction', $function->text, '14'))->_id
        );
    }

    public function testGetFunctionNew(): void
    {
        $mock = $this->getMockWithoutConstruct();

        $function_text = uniqid();
        $function      = $this->invokePrivateMethod($mock, 'getFunction', $function_text, '14');

        $this->assertNotNull($function);
        $this->assertEquals($function_text, $function->text);
        $this->assertEquals('administratif', $function->type);
    }

    public function testCheckProfileWithId(): void
    {
        $mock = $this->getMockWithoutConstruct();

        $profile           = new CUser();
        $profile->template = 1;
        $profile->loadMatchingObjectEsc();

        $mediuser = new CMediusers();
        $this->assertNull($mediuser->_profile_id);
        $this->invokePrivateMethod($mock, 'checkProfile', $mediuser, $profile->_id);
        $this->assertEquals($profile->_id, $mediuser->_profile_id);
    }

    public function testCheckProfileWithName(): void
    {
        $mock = $this->getMockWithoutConstruct();

        $profile           = new CUser();
        $profile->template = 1;
        $profile->loadMatchingObjectEsc();

        $mediuser = new CMediusers();
        $this->assertNull($mediuser->_profile_id);
        $this->invokePrivateMethod($mock, 'checkProfile', $mediuser, $profile->user_username);
        $this->assertEquals($profile->_id, $mediuser->_profile_id);
    }

    private function getMockWithoutConstruct(array $functions = []): CCSVImportMediusers
    {
        return $this->getMockBuilder(CCSVImportMediusers::class)
            ->disableOriginalConstructor()
            ->onlyMethods($functions)
            ->getMock();
    }
}
