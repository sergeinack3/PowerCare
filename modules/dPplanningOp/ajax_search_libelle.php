<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CLibelleOp;

CCanDo::checkEdit();
$page = CValue::get("page", "0");
$nom  = CValue::get("nom");

$where = array();
$where["group_id"] = " = '".CGroups::loadCurrent()->_id."'";
if ($nom) {
  $where["nom"]    = " LIKE '%$nom%'";
}

$libelle    = new CLibelleOp();
$total_libs = $libelle->countList($where);
$libelles   = $libelle->loadGroupList($where, "nom", "$page, 50");


$smarty = new CSmartyDP();
$smarty->assign('libelles'   , $libelles);
$smarty->assign("nom"        , $nom);
$smarty->assign("page"       , $page);
$smarty->assign("total_libs" , $total_libs);
$smarty->display("inc_search_libelle");