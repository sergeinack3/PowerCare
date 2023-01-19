<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\DataModels;

use Exception;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;

class CJfseUserTest extends UnitTestJfse
{
    public function testGetSpec(): void
    {
        $expected                     = new CMbObjectSpec();
        $expected->table              = "jfse_users";
        $expected->key                = "jfse_user_id";
        $expected->uniques['jfse_id'] = ['jfse_id'];

        $this->assertEquals($expected, (new CJfseUser())->getSpec());
    }

    public function testGetProps(): void
    {
        $expected = [
            '_shortview'            => 'str',
            '_view'                 => 'str',
            'jfse_user_id'          => 'ref class|CJfseUser show|0',
            'jfse_id'               => 'str notNull',
            'mediuser_id'           => 'ref class|CMediusers back|jfse_user',
            'jfse_establishment_id' => 'ref class|CJfseEstablishment back|establishment',
            'creation'              => 'dateTime notNull default|now',
            'securing_mode'         => 'enum list|3|4 default|3'
        ];

        $this->assertEquals($expected, (new CJfseUser())->getProps());
    }

    public function testUniqueJfseIdConstraintFailure(): void
    {
        $user = new CJfseUser();
        $user->jfse_id = 515165198;
        $user->loadMatchingObject();

        if (!$user->_id) {
            $user->store();
        }

        $duplicate = new CJfseUser();
        $duplicate->jfse_id = 515165198;
        $result = $duplicate->store();

        $this->assertStringContainsString(
            'CJfseUser-failed-jfse_id',
            $result,
            'JfseId unique constraint on CJfseUser not working properly'
        );
    }

    public function testStoreJfseUserCreationDate(): void
    {
        $user = new CJfseUser();
        $user->jfse_id = 987615618;
        $result = $user->store();

        $this->assertNotEmpty($user->creation, "CJfseUser-creation is empty");
    }

    /**
     * @throws Exception
     */
    public function testLoadMediuser(): void
    {
        $mediuser = (new CMediusersGenerator())->generate();

        $user = new CJfseUser();
        $user->setMediuserId($mediuser->_id);

        $this->assertEquals($mediuser->_id, $user->loadMediuser()->_id);
    }

    public function testLoadMediuserWithoutMediuserId(): void
    {
        $this->assertEquals(new CMediusers(), (new CJfseUser())->loadMediuser());
    }

    public function testSetMediuserIdWithUnknownId(): void
    {
        $this->expectExceptionMessage('MediuserNotFound');

        $user = new CJfseUser();
        $user->setMediuserId(0);
    }
}
