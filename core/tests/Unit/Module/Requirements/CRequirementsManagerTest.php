<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module\Requirements;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectConfig;
use Ox\Core\Module\Requirements\CRequirementsException;
use Ox\Core\Module\Requirements\CRequirementsItem;
use Ox\Core\Module\Requirements\CRequirementsManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Description
 */
class CRequirementsManagerTest extends OxUnitTestCase {

  /**
   * @throws ReflectionException
   */
  public function testCheckRequirements() {
    $requirements_test = new CRequirementsDummy();
    $actual            = $requirements_test->checkRequirements();

    $this->assertFalse($actual);
  }

  /**
   * @throws ReflectionException
   */
  public function testSerialize() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    $actual = $requirements_test->serialize(false);

    self::assertEquals(count($requirements_test), count($actual));

    $requirement_item = reset($actual);

    self::assertArrayHasKey('tab', $requirement_item);
    self::assertArrayHasKey('group', $requirement_item);
    self::assertArrayHasKey('section', $requirement_item);
    self::assertArrayHasKey('description', $requirement_item);
    self::assertArrayHasKey('expected', $requirement_item);
    self::assertArrayHasKey('actual', $requirement_item);
    self::assertArrayHasKey('check', $requirement_item);
  }

  /**
   * @throws ReflectionException
   */
  public function testSerializeGroup() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    $actual = $requirements_test->serialize();
    $this->assertEquals(count($requirements_test->getTabs()), count($actual));

    // assert on tab
    $this->assertArrayHasKey('test_tab', $actual);
    $this->assertArrayHasKey('test_tab3', $actual);
    $this->assertArrayHasKey(CRequirementsItem::TAB_UNDEFINED, $actual);

    // assert on group
    $group_test_tab = $actual["test_tab"];
    $this->assertEquals(1, count($group_test_tab));
    $this->assertArrayHasKey("test_group", $group_test_tab);
    $group_test_tab = $group_test_tab["test_group"];
    $this->assertArrayHasKey(CRequirementsItem::GROUP_UNDEFINED, $group_test_tab);
    $group_test_tab = $group_test_tab[CRequirementsItem::GROUP_UNDEFINED];

    // assert on item
    $item = reset($group_test_tab);
    $this->assertArrayHasKey('expected', $item);
    $this->assertArrayHasKey('actual', $item);
    $this->assertArrayHasKey('description', $item);
    $this->assertArrayHasKey('check', $item);
  }

  /**
   * @throws ReflectionException
   */
  public function testCountRequirementsErrors() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    self::assertEquals(2, $requirements_test->countErrors());
  }

  /**
   * @throws ReflectionException
   */
  public function testCountRequirementsCheck() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    self::assertEquals(5, $requirements_test->count());
    self::assertEquals(5, count($requirements_test));
  }

  /**
   * @throws ReflectionException
   */
  public function testCountGroups() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    $groups = $requirements_test->getGroups();
    $this->assertEquals(2, count($groups));
  }

  /**
   * @throws ReflectionException
   */
  public function testCountRequirements() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();

    foreach ($requirements_test as $item) {
      $this->assertInstanceOf(CRequirementsItem::class, $item);
    }
  }

  /**
   * @throws ReflectionException
   */
  public function testCountable() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();
    $items = $requirements_test->getItems();
    $this->assertCountableCount($requirements_test, count($items));
  }

  /**
   * @throws ReflectionException
   */
  public function testIterable() {
    $requirements_test = new CRequirementsDummy();
    $requirements_test->checkRequirements();
    $items = $requirements_test->getItems();
    $this->assertIterableCount($requirements_test, $items, count($items));
  }

  /**
   * @throws TestsException
   */
  public function testGetTag() {
    $doc = "Documentations\n\n * @tab foo \n\n * @group foo bar\n will not be get";
    $dummy = new CRequirementsDummy();

    $expected = "foo";
    $actual = $this->invokePrivateMethod($dummy, "getTag", $doc, 'tab');
    $this->assertEquals($expected, $actual);

    $expected = "foo bar";
    $actual = $this->invokePrivateMethod($dummy, "getTag", $doc, 'group');
    $this->assertEquals($expected, $actual);

    $expected = "foo";
    $actual = $this->invokePrivateMethod($dummy, "getTag", $doc, ' tab');
    $this->assertEquals($expected, $actual);

    $expected = null;
    $actual = $this->invokePrivateMethod($dummy, "getTag", $doc, 'foo');
    $this->assertEquals($expected, $actual);
  }

  /**
   * @return array
   */
  public function providerAssertEquals() {
    return [
      ["toto", "toto"],
      [false, false],
      [true, true],
      [1, 1],
      ["toti", "toto", false],
      [false, true, false],
      [true, false, false],
      [1, 42, false],
    ];
  }

  /**
   * @param CRequirementsManager $requirements
   *
   * @return CRequirementsItem
   */
  private function getLastItem(CRequirementsManager $requirements): CRequirementsItem {
    $items = $requirements->getItems();
    return end($items);
  }

  /**
   * @param mixed $actual
   * @param mixed $expected
   * @param bool  $type_assert
   *
   * @throws TestsException
   * @dataProvider providerAssertEquals
   */
  public function testAssertEquals($actual, $expected, bool $type_assert = true) {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertEquals", $actual, $expected, "description");
    $this->assertCount(1, $dummy);

    $item = $this->getLastItem($dummy);
    if ($type_assert === true) {
      $this->assertTrue($item->isCheck());
    }
    else {
      $this->assertFalse($item->isCheck());
    }
  }

  /**
   * @throws TestsException
   */
  public function testAssertNotNull() {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertNotNull", "foo", "description");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertNotNull", "", "description");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertNotNull", null, "description");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws TestsException
   */
  public function testAssertTrue() {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertTrue", true, true, "description");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertTrue", false, true, "description");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws TestsException
   */
  public function testAssertRegex() {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertRegex", "foo", "/foo/", "description");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertRegex", "bar", "/foo/", "description");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @dataProvider providerAssertEquals
   *
   * @param mixed $actual
   * @param mixed $expected
   * @param bool  $assert_type
   *
   * @throws TestsException
   */
  public function testAssertNotEquals($actual, $expected, bool $assert_type = true) {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertNotEquals", $actual, $expected, "description");
    $item = $this->getLastItem($dummy);

    if (!$assert_type) {
      $this->assertTrue($item->isCheck());
    }
    else {
      $this->assertFalse($item->isCheck());
    }
  }


  /**
   * @throws TestsException
   */
  public function testAssertFalse() {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertFalse", false, false, "description");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertFalse", true, false, "description");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws TestsException
   */
  public function testAssertModulesActived() {
    $dummy  = new CRequirementsDummy();
    $this->invokePrivateMethod($dummy, "assertModulesActived", ['foo', 'bar']);

    $this->assertCount(2, $dummy);
  }

  /**
   * @throws TestsException
   */
  public function testAssertObjectFieldNotNull() {
    $dummy  = new CRequirementsDummy();
    $object = new CMbObject();
    $object->_id = 42;
    $this->invokePrivateMethod($dummy, "assertObjectFieldNotNull", $object, "_id");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $object->_id = null;
    $this->invokePrivateMethod($dummy, "assertObjectFieldNotNull", $object, "_id");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws TestsException
   */
  public function testAssertObjectFieldTrue() {
    $dummy  = new CRequirementsDummy();
    $object = new CMbObject();
    $object->foo = true;
    $this->invokePrivateMethod($dummy, "assertObjectFieldTrue", $object, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $object->foo = false;
    $this->invokePrivateMethod($dummy, "assertObjectFieldTrue", $object, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws TestsException
   */
  public function testAssertObjectFieldFalse() {
    $dummy  = new CRequirementsDummy();
    $object = new CMbObject();
    $object->foo = false;
    $this->invokePrivateMethod($dummy, "assertObjectFieldFalse", $object, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $object->foo = true;
    $this->invokePrivateMethod($dummy, "assertObjectFieldFalse", $object, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @return array
   */
  public function providerAssertObjectFieldEquals() {
    return [
      ["toto", "toto"],
      [false, false],
      [true, true],
      [1, 1],
      ["toti", "toto", false],
      [false, true, false],
      [true, false, false],
      [1, 42, false],
    ];
  }

  /**
   * @param mixed $actual
   * @param mixed $expected
   * @param bool  $assert_type
   *
   * @throws TestsException
   * @dataProvider providerAssertEquals
   */
  public function testAssertObjectFieldEquals($actual, $expected, bool $assert_type = true) {
    $dummy  = new CRequirementsDummy();
    $object = new CMbObject();
    $object->foo = $actual;
    $this->invokePrivateMethod($dummy, "assertObjectFieldEquals", $object, "foo", $expected);
    $item = $this->getLastItem($dummy);
    if ($assert_type) {
      $this->assertTrue($item->isCheck());
    } else {
      $this->assertFalse($item->isCheck());
    }
  }

  /**
   * @config [CConfiguration] soins Other ignore_allergies bar|foo|test
   * @config [CConfiguration] dPhospi prestations systeme_prestations bar|foo|test
   * @throws TestsException
   */
  public function testAssertGConfRegex() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertGConfRegex", "soins Other ignore_allergies", "/foo/");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertGConfRegex", "dPhospi prestations systeme_prestations", "/fooo/");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config system object_handlers CSaEventObjectHandler 1
   * @config system object_handlers CAppFineClientHandler 0
   * @throws TestsException
   */
  public function testAssertConfTrue() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertConfTrue", "system object_handlers CSaEventObjectHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertConfTrue", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config system object_handlers CAppFineClientHandler 0
   * @config system object_handlers CSaEventObjectHandler 1
   * @throws TestsException
   */
  public function testAssertConfFalse() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertConfFalse", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertConfFalse", "system object_handlers CSaEventObjectHandler");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @return array
   */
  public function providerAssertConfEquals() {
    return [
      ["system object_handlers CAppFineClientHandler", "1"],
      ["system object_handlers CSaEventObjectHandler", "0"],
    ];
  }

  /**
   * @dataProvider providerAssertConfEquals
   *
   * @param string $path
   * @param mixed  $expected
   *
   * @throws TestsException
   * @config       system object_handlers CAppFineClientHandler 1
   * @config       system object_handlers CSaEventObjectHandler 0
   * @config       webservices soap_server_encoding foo
   */
  public function testAssertConfEquals($path, $expected) {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertConfEquals", $path, $expected);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
  }

  /**
   * @config system object_handlers CAppFineClientHandler 0
   * @throws TestsException
   */
  public function testAssertConfNotNull() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertConfNotNull", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
  }

  /**
   * @config [CConfiguration] system object_handlers CAppFineClientHandler 1
   * @config [CConfiguration] system object_handlers CSaEventObjectHandler 0
   * @throws TestsException
   */
  public function testAssertGConfTrue() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertGConfTrue", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertGConfTrue", "system object_handlers CSaEventObjectHandler");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config [CConfiguration] system object_handlers CAppFineClientHandler 0
   * @config [CConfiguration] system object_handlers CSaEventObjectHandler 1
   * @throws TestsException
   */
  public function testAssertGConfFalse() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertGConfFalse", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertGConfFalse", "system object_handlers CSaEventObjectHandler");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config [CConfiguration] system object_handlers CAppFineClientHandler 0
   * @throws TestsException
   */
  public function testAssertGConfNotNull() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertGConfNotNull", "system object_handlers CAppFineClientHandler");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $this->invokePrivateMethod($dummy, "assertGConfNotNull", "system foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

    /**
     * @return array
     */
    public function providerAssertGConfEquals() {
        return [
            ["system object_handlers CAppFineClientHandler", "1"],
            ["system object_handlers CSaEventObjectHandler", "0"],
            ["hl7 CHL7 sending_application", "foo"],
        ];
    }

  /**
   * @dataProvider providerAssertGConfEquals
   *
   * @param string $path
   * @param mixed  $expected
   *
   * @throws TestsException
   * @config [CConfiguration] system object_handlers CAppFineClientHandler 1
   * @config [CConfiguration] system object_handlers CSaEventObjectHandler 0
   * @config [CConfiguration] hl7 CHL7 sending_application foo
   */
  public function testAssertGConfEquals(string $path, $expected) {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    $this->invokePrivateMethod($dummy, "assertGConfEquals", $path, $expected);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
  }

  /**
   * @return array
   */
  public function providerAssertObjectConf() {
    return [
      ["foo", "1"],
      ["foo", "0"],
      ["foo", "foo"],
    ];
  }

  /**
   * @param $field
   * @param $expected
   * @dataProvider providerAssertObjectConf
   * @throws Exception
   */
  public function testAssertObjectConfEquals($field, $expected) {
    $dummy                               = new CRequirementsDummy();
    $groups                              = new CGroups();
    $groups->_ref_object_configs         = new CMbObjectConfig();
    $groups->_ref_object_configs->$field = $expected;
    $this->invokePrivateMethod($dummy, "assertObjectConfEquals", $groups, $field, $expected);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
  }

  /**
 * @throws Exception
 */
  public function testAssertObjectConfTrue() {
    $dummy                               = new CRequirementsDummy();
    $groups                              = new CGroups();
    $groups->_ref_object_configs         = new CMbObjectConfig();
    $groups->_ref_object_configs->foo = true;
    $this->invokePrivateMethod($dummy, "assertObjectConfTrue", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $groups->_ref_object_configs->foo = false;
    $this->invokePrivateMethod($dummy, "assertObjectConfTrue", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws Exception
   */
  public function testAssertObjectConfFalse() {
    $dummy                               = new CRequirementsDummy();
    $groups                              = new CGroups();
    $groups->_ref_object_configs         = new CMbObjectConfig();
    $groups->_ref_object_configs->foo = false;
    $this->invokePrivateMethod($dummy, "assertObjectConfFalse", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $groups->_ref_object_configs->foo = true;
    $this->invokePrivateMethod($dummy, "assertObjectConfFalse", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws Exception
   */
  public function testAssertObjectConfRegex() {
    $dummy                               = new CRequirementsDummy();
    $groups                              = new CGroups();
    $groups->_ref_object_configs         = new CMbObjectConfig();
    $groups->_ref_object_configs->foo = "bar|foo|test";
    $this->invokePrivateMethod($dummy, "assertObjectConfRegex", $groups, "foo", "/foo/");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $groups->_ref_object_configs->foo = "bar|test";
    $this->invokePrivateMethod($dummy, "assertObjectConfRegex", $groups, "foo", "/foo/");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws Exception
   */
  public function testAssertObjectConfNotNull() {
    $dummy                               = new CRequirementsDummy();
    $groups                              = new CGroups();
    $groups->_ref_object_configs         = new CMbObjectConfig();
    $groups->_ref_object_configs->foo = false;
    $this->invokePrivateMethod($dummy, "assertObjectConfNotNull", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    $groups->_ref_object_configs->foo = null;
    $this->invokePrivateMethod($dummy, "assertObjectConfNotNull", $groups, "foo");
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @throws CRequirementsException
   */
  public function testException() {
    $this->expectException(CRequirementsException::class);
    throw new CRequirementsException(CRequirementsException::TOO_MUCH_REQUIREMENTS_CLASS);
  }

  /**
   * @config ref_pays 2
   * @return void
   * @throws Exception
   */
  public function testAssertConfEqualsOrGreater() {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    // true
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrGreater", 'ref_pays', 0);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrGreater", 'ref_pays', 2);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    // false
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrGreater", 'ref_pays', 3);
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config ref_pays 2
   * @return void
   * @throws Exception
   */
  public function testAssertConfEqualsOrLess (): void {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    // true
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrLess", 'ref_pays', 3);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrLess", 'ref_pays', 2);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    // false
    $this->invokePrivateMethod($dummy, "assertConfEqualsOrLess", 'ref_pays', 0);
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config [CConfiguration] addictologie CNoteSuite delay_lock_note 2
   * @return void
   * @throws Exception
   */
  public function testAssertGConfEqualsOrGreater (): void {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    // true
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrGreater", 'addictologie CNoteSuite delay_lock_note', 0);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrGreater", 'addictologie CNoteSuite delay_lock_note', 2);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    // false
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrGreater", 'addictologie CNoteSuite delay_lock_note', 3);
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }

  /**
   * @config [CConfiguration] addictologie CNoteSuite delay_lock_note 2
   * @return void
   * @throws Exception
   */
  public function testAssertGConfEqualsOrLess(): void {
    $dummy = new CRequirementsDummy();
    $dummy->setEstablishment(CGroups::loadCurrent());
    // true
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrLess", 'addictologie CNoteSuite delay_lock_note', 3);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrLess", 'addictologie CNoteSuite delay_lock_note', 2);
    $item = $this->getLastItem($dummy);
    $this->assertTrue($item->isCheck());

    // false
    $this->invokePrivateMethod($dummy, "assertGConfEqualsOrLess", 'addictologie CNoteSuite delay_lock_note', 0);
    $item = $this->getLastItem($dummy);
    $this->assertFalse($item->isCheck());
  }
}
