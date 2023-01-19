<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbSecurity;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CDoCreateSecondaryMediuser
 */
class CDoCreateSecondaryMediuser extends CDoObjectAddEdit {
  public $main_user_id;
  public $adeli;

  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CMediusers", "user_id");
    $this->main_user_id = CView::post('main_user_id', 'ref class|CMediusers');
    $this->adeli = CView::post('adeli', 'str');
    CView::checkin();
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    $main_mediuser = CMediusers::get($this->main_user_id);
    $main_user = $main_mediuser->loadRefUser();
    $main_user->_duplicate = true;
    $main_user->_duplicate_username = CMbSecurity::getRandomAlphaNumericString();

    $user = $main_user->duplicateUser(false);
    if ($user && $user->_id) {
      $mediuser               = $user->loadRefMediuser();
      $mediuser->adeli        = $this->adeli;
      $mediuser->main_user_id = $this->main_user_id;
      if ($msg = $mediuser->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg('CMediusers-msg-secondary_user_created', UI_MSG_OK);
      }

      // Redirection
      if ($this->redirectStore) {
        CAppUI::redirect($this->redirectStore);
      }

    }
  }
}

$do = new CDoCreateSecondaryMediuser();
$do->doIt();