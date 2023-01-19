<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageSeanceCollective;
use Ox\Mediboard\Ssr\CPlateauTechnique;
use Ox\Mediboard\Ssr\CTrameSeanceCollective;

global $g, $m;

CCanDo::checkEdit();
$plage_id    = CView::get("plage_id", "ref class|CPlageSeanceCollective");
$user_id     = CView::get("user_id", "ref class|CMediusers", true);
$function_id = CView::get("function_id", "ref class|CFunctions", true);
$trame_id    = CView::get("trame_id", "ref class|CTrameSeanceCollective", true);
CView::checkin();

$plage = new CPlageSeanceCollective();
$plage->load($plage_id);

if (!$plage->_id) {
  $plage->group_id = $g;
  $plage->trame_id = $trame_id;
  $plage->user_id  = $user_id;
  $plage->type     = $m;
}
else {
  $plage->loadRefElementPrescription()->loadRefsCodesSSR();
  $plage->loadRefsSejoursAffectes();
  $plage->loadRefEquipement();
  $plage->loadRefsAllIntervenant();
}
$plage->loadRefsActes();
$plage->rangeActesOther();
$plage->isInactivable();

$trame              = new CTrameSeanceCollective();
$trame->function_id = $function_id;
$trame->type        = $m;
$trame->group_id    = $g;
$trames             = $trame->loadMatchingList("nom");

$kines       = CEvenementSSR::loadRefExecutants($g, $function_id);
$kines_cdarr = array();
foreach ($kines as $_kine) {
  if ($_kine->code_intervenant_cdarr) {
    $kines_cdarr[$_kine->_id] = $_kine;
  }
}

// Chargement de tous les plateaux et des equipements et techniciens associés
$where        = array();
$where[]      = "type = '$m' OR type IS NULL";
$plateau_tech = new CPlateauTechnique();
$plateaux     = $plateau_tech->loadGroupList($where);
CMbObject::massLoadBackRefs($plateaux, "equipements", "nom ASC");
/** @var CPlateauTechnique[] $plateaux */
foreach ($plateaux as $_plateau) {
  $_plateau->loadRefsEquipements();
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("plage", $plage);
$smarty->assign("kines", $kines);
$smarty->assign("kines_cdarr", $kines_cdarr);
$smarty->assign("trames", $trames);
$smarty->assign("plateaux", $plateaux);
$smarty->display("vw_edit_plage_collective");
