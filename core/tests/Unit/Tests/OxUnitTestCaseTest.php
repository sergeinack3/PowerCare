<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Tests;

use Error;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\Tests\Unit\Models\CUnitTest;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;

class OxUnitTestCaseTest extends OxUnitTestCase
{
    /**
     * @param CModule $module
     *
     * @throws Exception
     */
    public function testToogleModule(): void
    {
        $module           = new CModule();
        $module->mod_name = "Appfine";
        $module->loadMatchingObject();

        $is_active = $module->mod_active;

        $msg = static::toogleActiveModule($module);
        $this->assertNull($msg);
        $this->asserttrue($is_active !== $module->mod_active);

        static::toogleActiveModule($module);
    }

    /**
     *
     */
    public function testCurrentUser(): void
    {
        $user = CUser::get();
        $this->assertEquals(strtolower('Phpunit'), strtolower($user->user_first_name));
    }

    /**
     * SetConfig
     *
     * @config ref_pays 2
     */
    public function testSetConfig(): void
    {
        $this->assertEquals(CAppUI::conf("ref_pays"), 2);
    }


    /**
     * @return void
     * @throws TestsException
     */
    public function testInvokePrivateStaticMethod(): void
    {
        $method = 'privateStaticMethod';
        $this->assertTrue($this->invokePrivateMethod(new CUnitTest(), $method));
        $this->assertTrue($this->invokePrivateMethod(CUnitTest::class, $method));
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function testInvokePrivateMethod(): void
    {
        $default_return = 'default';
        $method_name    = 'privateMethod';
        $args           = ['lorem', 'ipsum'];
        $other_args     = 'other_args';
        $obj            = new CUnitTest();
        $class_name     = CUnitTest::class;

        // whitout params
        $return = $this->invokePrivateMethod($obj, $method_name);
        $this->assertEquals($return, $default_return);

        $return = $this->invokePrivateMethod($class_name, $method_name);
        $this->assertEquals($return, $default_return);

        // with args
        $return = $this->invokePrivateMethod($obj, $method_name, $args);
        $this->assertEquals($return, $args);

        $return = $this->invokePrivateMethod($class_name, $method_name, $args);
        $this->assertEquals($return, $args);

        // with other args
        $return = $this->invokePrivateMethod($obj, $method_name, $args, $other_args);
        $this->assertEquals($return, $other_args);

        $return = $this->invokePrivateMethod($class_name, $method_name, $args, $other_args);
        $this->assertEquals($return, $other_args);
    }

    /**
     * @return void
     */
    public function testInvokePrivateMethodFaild(): void
    {
        $method_name = 'privateMethod';
        $obj         = new CUnitTest();

        // this is private
        $this->expectException(Error::class);
        $obj->$method_name();
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function testGetPrivateConst(): void
    {
        $const_name = 'PRIVATE_CONST';
        $expected   = 'PRIVATE';
        $obj        = new CUnitTest();

        $this->assertEquals($expected, $this->getPrivateConst($obj, $const_name));
        $this->assertEquals($expected, $this->getPrivateConst(CUnitTest::class, $const_name));
    }

    /**
     * @param string|object $obj
     * @param string        $const_name
     *
     * @dataProvider getPrivateConstFailedProvider
     *
     * @return void
     * @throws TestsException
     */
    public function testGetPrivateConstFailed($obj, $const_name): void
    {
        $this->expectException(TestsException::class);
        $this->getPrivateConst($obj, $const_name);
    }

    /**
     * @return array
     */
    public function getPrivateConstFailedProvider(): array
    {
        return [
            'const_already_public'     => [new CUnitTest(), 'PUBLIC_CONST'],
            'class_does_not_exists'    => ['Not a class', 'PRIVATE_CONST'],
            'constante_does_no_exists' => [new CUnitTest(), 'NON_EXISTING_CONST'],
        ];
    }

    public function testGetObjectFromFixturesReferenceFailed(): void
    {
        $this->expectException(TestsException::class);
        $u = $this->getObjectFromFixturesReference(CUser::class, uniqid('reference_'));
    }

    public function testGetObjectFromFixturesReferenceSuccess(): void
    {
        $u = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $this->assertInstanceOf(CUser::class, $u);
    }

    public function testGetObjectFromFixturesReferenceCache(): void
    {
        $u1 = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $u2 = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        // Deep compare objects, they must be exactly the same reference
        $this->assertTrue($u1 === $u2);
    }

    /**
     * @throws Exception
     */
    public function testCloneModelObjectInTests(): void
    {
        /** @var CPatient $object */
        $object = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        /** @var CPatient $new_object */
        $new_object = $this->cloneModelObject($object);

        $this->assertEquals($object->nom, $new_object->nom);
        $this->assertNotEquals($object->_id, $new_object->_id);
    }

    /**
     * @throws Exception
     */
    public function testCloneModelObjectFromFixture(): void
    {
        /** @var CPatient $object */
        $object = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        /** @var CPatient $object_clone */
        $object_clone = $this->getObjectFromFixturesReference(
            CPatient::class,
            SimplePatientFixtures::SAMPLE_PATIENT,
            true
        );

        $this->assertEquals($object->nom, $object_clone->nom);
        $this->assertNotEquals($object->_id, $object_clone->_id);
    }
}
