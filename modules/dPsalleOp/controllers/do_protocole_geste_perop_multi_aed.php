<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkEdit();
$geste_perop_dates = CView::post("_geste_perop_dates", "str");
$geste_perop_ids   = CView::post("_geste_perop_ids", "str");
$operation_id      = CView::post("operation_id", "ref class|COperation");
CView::checkin();

$gestes_dates     = json_decode(stripslashes($geste_perop_dates), true);
$gestes_ids       = json_decode(stripslashes($geste_perop_ids));
$counter          = 0;
$structure_gestes = array();
$current_user     = CMediusers::get();

CMbArray::removeValue(null,$gestes_dates);

$gestes_ids = !is_array($gestes_ids) ? array($gestes_ids) : $gestes_ids;

foreach ($gestes_ids as $_datas) {
  $structure_gestes = explode("|", $_datas);

  $geste        = CGestePerop::find($structure_gestes[0]);
  $count_values = count($structure_gestes);

  if ($geste->_id) {
    $anesth_perop                 = new CAnesthPerop();
    $anesth_perop->libelle        = $geste->libelle;
    $anesth_perop->geste_perop_id = $geste->_id;
    $anesth_perop->categorie_id   = $geste->categorie_id;
    $anesth_perop->datetime       = $gestes_dates[$geste->_id];
    $anesth_perop->operation_id   = $operation_id;
    $anesth_perop->user_id        = $current_user->_id;

    if ($count_values >= 2) {
      $anesth_perop->geste_perop_precision_id = $structure_gestes[1];
    }

    if ($count_values >= 3) {
      $anesth_perop->precision_valeur_id = $structure_gestes[2];
    }

    if ($msg = $anesth_perop->store()) {
      return $msg;
    }
    $counter++;
  }
}

CAppUI::displayMsg($msg, CAppUI::tr("CAnesthPerop-msg-create") . " x $counter");

echo CAppUI::getMsg();
CApp::rip();
