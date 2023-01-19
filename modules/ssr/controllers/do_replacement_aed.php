<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbArray;

$do = new CDoObjectAddEdit('CReplacement');

if ($sejour_ids = CMbArray::extract($_POST, "sejour_ids")) {
  $do->redirect = null;
  foreach (explode("-", $sejour_ids) as $sejour_id) {
    $_POST["sejour_id"] = $sejour_id;
    $do->doIt();
  }
  echo CAppUI::getMsg();
  CApp::rip();
}

$do->doIt();
