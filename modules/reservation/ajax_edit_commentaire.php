<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Reservation\CCommentairePlanning;

CCanDo::checkEdit();

$commentaire_id = CValue::get("commentaire_id");
$clone          = CValue::get("clone", false);
$date           = CValue::get("date");
$hour           = CValue::get("hour");
$salle_id       = CValue::get("salle_id");
$callback       = CValue::get("callback");

$commentaire = new CCommentairePlanning();
$commentaire->load($commentaire_id);

if (!$commentaire->_id) {
  $commentaire->debut    = "$date $hour:00:00";
  $commentaire->fin      = "$date " . ($hour + 1) . ":00:00";
  $commentaire->salle_id = $salle_id;
}

if ($clone) {
  $commentaire->_id = null;
}

$smarty = new CSmartyDP();

$smarty->assign("commentaire", $commentaire);
$smarty->assign("clone", $clone);
$smarty->assign("callback", $callback);

$smarty->display("inc_edit_commentaire");
