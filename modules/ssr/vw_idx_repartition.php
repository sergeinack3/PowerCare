<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CPlateauTechnique;

global $m;

CCanDo::checkRead();

$date     = CView::get("date", "date default|now", true);
$readonly = CView::get("readonly", "bool default|0");
CView::checkin();

// Plateaux disponibles
$where                = array();
$where[]              = "type = '$m' OR type IS NULL";
$where["repartition"] = " = '1'";
$plateau              = new CPlateauTechnique();
$plateaux             = $plateau->loadGroupList($where);

$techniciens = array("nb" => 0, "plateau" => 0);
/** @var CPlateauTechnique[] $plateaux */
foreach ($plateaux as $_plateau) {
  $_plateau->loadRefsTechniciens();
  foreach ($_plateau->_ref_techniciens as $_technicien) {
    $_technicien->loadRefCongeDate($date);
  }
  $techniciens["nb"] += count($_plateau->_ref_techniciens);
  if (count($_plateau->_ref_techniciens) == 1) {
    $techniciens["plateau"] = $_plateau->_id;
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("date", $date);
$smarty->assign("plateaux", $plateaux);
$smarty->assign("techniciens", $techniciens);
$smarty->assign("bilan", new CBilanSSR());
$smarty->assign("readonly", $readonly);
$smarty->display("vw_idx_repartition");
