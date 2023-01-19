<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

CCanDo::checkEdit();
$geste_ids = CView::get("geste_ids", "str");
CView::checkin();

$geste_ids = explode("|", $geste_ids);
$gestes    = array();

foreach ($geste_ids as $_geste_id) {
  $geste = CMbObject::loadFromGuid("CGestePerop-" . $_geste_id);

  if ($geste->_id) {
    $gestes[$_geste_id] = $geste;
  }
}

$precision   = new CGestePeropPrecision();
$precisions = $precision->loadGroupList(null, "libelle ASC");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("gestes"    , $gestes);
$smarty->assign("precisions", $precisions);
$smarty->display("inc_vw_choose_precisions_gestes");
