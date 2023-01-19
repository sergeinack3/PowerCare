<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CRedon;

CCanDo::checkEdit();

$redons    = CView::post("redons", "str");
$sejour_id = CView::post("sejour_id", "ref class|CSejour");

CView::checkin();

if (!is_array($redons)) {
  return;
}

foreach ($redons as $_cste => $_status) {
  $_redon = new CRedon();
  $_redon->constante_medicale = $_cste;
  $_redon->sejour_id = $sejour_id;

  $_redon_id = $_redon->loadMatchingObject();

  if ($_status || $_redon->_id) {
    $_redon->actif = $_status;

    if (!$_redon->_id) {
      $_redon->sous_vide = 1;
    }

    $msg = $_redon->store();

    CAppUI::setMsg($msg ? : ("CRedon-msg-" . ($_redon_id ? "modify" : "create")), $msg ? UI_MSG_ERROR : UI_MSG_OK);
  }
}

echo CAppUI::getMsg();
