<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbPhone;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Tests\OxUnitTestCase;

/**
 * CMbPhone test class
 */
class CMbPhoneTest extends OxUnitTestCase
{
    public function testPhoneToInternational()
    {
        $phone1 = '0602030405';
        $phone2 = '+33902030405';
        $phone3 = '33902030405';

        $this->assertEquals('33602030405', CMbPhone::phoneToInternational($phone1));
        $this->assertEquals('33902030405', CMbPhone::phoneToInternational($phone2));
        $this->assertEquals($phone3, CMbPhone::phoneToInternational($phone3));
    }

    public function testCheckMobileNumber()
    {
        $this->assertTrue(CMbPhone::checkMobileNumber('0602030405'));
        $this->assertTrue(CMbPhone::checkMobileNumber('0602030405', 'de'));
        $this->assertFalse(CMbPhone::checkMobileNumber('0902030405'));
    }

    public function testGetMobilePhoneFromGuidSuccess()
    {
        $mobile = "0602030405";
        /** @var CUser $object */
        $object              = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $object->user_mobile = $mobile;
        $this->storeOrFailed($object);

        $this->assertEquals($mobile, CMbPhone::getMobilePhoneFromGuid($object->_guid));
    }

    public function testGetMobilePhoneFromGuidNull()
    {
        /** @var CFunctions $object */
        $object           = CFunctions::getSampleObject();
        $object->group_id = CGroups::loadCurrent()->_id;
        $this->storeOrFailed($object);

        $this->assertNull(CMbPhone::getMobilePhoneFromGuid($object->_guid));
    }

    public function testGetPhoneFromGuidMobile()
    {
        $mobile = "0602030405";
        /** @var CUser $object */
        $object              = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $object->user_mobile = $mobile;
        $this->storeOrFailed($object);

        $this->assertEquals($mobile, CMbPhone::getPhoneFromGuid($object->_guid));
    }
}
