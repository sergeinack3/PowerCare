<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$nda = CValue::get("nda");

$idex                  = new CIdSante400();
$where["object_class"] = "= 'CSejour'";
$where["id400"]        = "LIKE '$nda%'";
/** @var CIdSante400[] $ideces */
$ideces  = $idex->loadList($where, null, "100");
$sejours = array();
foreach ($ideces as $_idex) {
  /** @var CSejour $sejour */
  $sejour = $_idex->loadTargetObject();
  $sejour->loadRefPatient()->loadIPP();
  /** @var CRHS $_rhs */
  foreach ($sejour->loadBackRefs("rhss", "date_monday") as $_rhs) {
    $_rhs->loadRefsNotes();
  }
  $sejours[$sejour->_id] = $sejour;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejours", $sejours);
$smarty->display("inc_vw_rhs_sejour_search");
