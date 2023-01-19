<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CMessageExterne extends CMbObject {

  public $account_id;     // account ID

  //behaviour
  public $archived;       //bool
  public $starred;        //bool
  public $date_read;      //dateTime
  public $date_received;  //date of mb received

  public $_ref_account;


  /**
   * @return \Ox\Core\CStoredObject|null
   * @throws \Exception
   */
  function loadRefAccount() {
    return $this->_ref_account = $this->loadFwdRef("account_id", true);
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["account_id"]    = "ref class|CMbObject notNull";
    $props["archived"]      = "bool notNull default|0";
    $props["starred"]       = "bool notNull default|0";
    $props["date_read"]     = "dateTime";
    $props["date_received"] = "dateTime";
    
    return $props;
  }
}