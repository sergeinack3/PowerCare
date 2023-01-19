<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
// Filtres d'affichage
$filter = new CSejour();
$filter->_date_entree = CValue::getOrSession("_date_entree", CMbDT::date("-1 day"));
$see_sejour_masse = CValue::get("see_sejour_masse", 0);

if ($see_sejour_masse) {
  $where = array();
  $where["annule"] = " = '0'";
  $where["sortie_reelle"] = " IS NULL";
  $where[] = "entree_reelle BETWEEN '$filter->_date_entree 00:00:00' AND '$filter->_date_entree 23:59:00'";
  $where[] = " type = 'exte' OR type = 'seances'";
  $sejour = new CSejour();
  $sejours = $sejour->loadGroupList($where, null, null, "sejour_id");

  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($sejours, "praticien_id");
  CStoredObject::massLoadFwdRef($sejours, "mode_sortie_id");
  $affectations = CStoredObject::massLoadBackRefs($sejours, "affectations");
  CAffectation::massUpdateView($affectations);
  foreach ($sejours as $_sejour) {
    /* @var CSejour $_sejour*/
    $_sejour->loadRefPatient();
    $_sejour->loadRefPraticien();
    $_sejour->loadRefModeSortie();
    $_sejour->loadRefsAffectations();
  }
}

$modes_sorties = CModeSortieSejour::listModeSortie();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter" , $filter);

if (!$see_sejour_masse) {
  $smarty->display("vw_sortie_masse.tpl");
}
else {
  $smarty->assign("sejours" , $sejours);
  $smarty->assign("modes_sorties", $modes_sorties);
  $smarty->display("inc_vw_sejours_masse.tpl");
}
