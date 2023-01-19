<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * CIM10
 */
$do = new CDoObjectAddEdit("CFavoriCIM10");

CView::checkin();

// Amélioration des textes
$user = new CMediusers;
$user->load($_POST["favoris_user"]);
$for = " pour $user->_view";
$do->createMsg .= $for;
$do->modifyMsg .= $for;
$do->deleteMsg .= $for;

$do->redirect = null;
$do->doIt();

if (CAppUI::pref("new_search_cim10") == 1) {
  echo CAppUI::getMsg();
  CApp::rip();
}
