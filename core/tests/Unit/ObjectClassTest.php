<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CClassMap;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\System\CObjectClass;
use Ox\Tests\OxUnitTestCase;


/**
 * Class ObjectClassTest
 */
class ObjectClassTest extends OxUnitTestCase {

  /**
   * @throws Exception
   *
   */
  public function testGetId() {
    // succes
    $object = new CSearchHistory();
    $class_name = CClassMap::getInstance()->getShortName($object);
    $class_id_1 = CObjectClass::getID($class_name);
    $class_id_2 = $object->getObjectClassID();
    $this->assertEquals($class_id_1, $class_id_2);

    // error
    $this->expectException(Exception::class);
    CObjectClass::getID(uniqid());
  }


  public function testList() {
    $objectClass = new CObjectClass();
    $list        = $objectClass->loadList();
    $this->assertInstanceOf(CObjectClass::class, reset($list));
  }

  public function testCreate(){
    $this->expectException(Exception::class);
    CObjectClass::getID("Lorem ipsum");
  }
}
