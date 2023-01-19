<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\System\CUserAction;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CUserActionTest
 */
class CUserActionTest extends OxUnitTestCase {

  /**
   * @return CUserAction
   */
  public function testStore() {

    $ip          = "127.0.0.1";
    $object      = new CSearchHistory();
    $object->_id = 1000;

    $userAction                  = new CUserAction();
    $userAction->object_class_id = $object->getObjectClassID();
    $userAction->object_id       = $object->_id;
    $userAction->type            = 'store';
    $userAction->ip_address      = $ip;
    $userAction->date            = CMbDT::dateTime();
    $userAction->user_id         = 1;

    $userAction->_datas = array(
      'bar'  => 'foo',
      'bar2' => 'food'
    );

    $retour = $userAction->store();

    $this->assertNull($retour);
    $this->assertNotNull($userAction->_id);

    $userAction->_datas                 = null;
    $userAction->_ref_user_action_datas = null;

    return $userAction;
  }


  /**
   * @param CUserAction $userAction
   *
   * @depends testStore
   * @return CUserAction
   */
  public function testLoad(CUserAction $userAction) {
    $_id           = $userAction->_id;
    $userActionNew = new CUserAction();
    $userActionNew->load($_id);


    $this->assertEquals($userAction, $userActionNew);

    return $userActionNew;
  }

  /**
   * @param CUserAction $userAction
   *
   * @depends testLoad
   */
  public function testLoadRefUserActionDatas(CUserAction $userAction) {
    $datas = $userAction->loadRefUserActionDatas();
    $this->assertInstanceOf('CUserActionData', reset($datas));
    $this->assertCount(2, $datas);
  }

  /**
   * @param CUserAction $userAction
   *
   * @depends testLoad
   */
  public function testGetOldValues(CUserAction $userAction) {
    $userAction->getOldValues();
    $this->assertNotNull($userAction->_old_values);
    $this->assertIsArray($userAction->_old_values);
  }


}
