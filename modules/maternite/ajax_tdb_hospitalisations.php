<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Liste des hospitalisations en cours du tableau de bord
 */
CCanDo::checkRead();

$date = CView::get("date", "date default|now");

CView::checkin();

$group = CGroups::loadCurrent();

$sejour                        = new CSejour();
$where                         = array();
$where["sejour.grossesse_id"]  = "IS NOT NULL";
if (CAppUI::gconf("maternite general vue_alternative")) {
  $where["sejour.entree_reelle"] = "IS NOT NULL";
}
$where["sejour.entree"]        = "<= '$date 23:59:59' ";
$where["sejour.sortie"]        = ">= '$date 00:00:00' ";
$where["sejour.group_id"]      = " = '$group->_id' ";
$where["sejour.annule"]        = "= '0'";
$where["sejour.sortie_reelle"] = "> '$date " . CMbDT::time() . "' OR sejour.sortie_reelle IS NULL";
$order                         = "sejour.entree DESC";

/** @var CSejour[] $listSejours */
$listSejours = $sejour->loadList($where, $order);

CSejour::massLoadCurrAffectation($listSejours, $date . " " . CMbDT::time());
$grossesses = CStoredObject::massLoadFwdRef($listSejours, "grossesse_id");
CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");
$naissances     = CStoredObject::massLoadBackRefs($grossesses, "naissances");
$sejours_enfant = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
$patientes      = CStoredObject::massLoadFwdRef($sejours_enfant, "patient_id");
CStoredObject::massLoadBackRefs($patientes, "bmr_bhre");

foreach ($listSejours as $_sejour) {
  $_sejour->loadRefLastOperation();
  $grossesse = $_sejour->loadRefGrossesse();
  $grossesse->loadRefParturiente()->updateBMRBHReStatus($_sejour);
  $grossesse->loadRefDossierPerinat();
  $naissances = $grossesse->loadRefsNaissances();
  foreach ($naissances as $_naissance) {
    $_naissance->loadRefSejourEnfant()->loadRefPatient();
    $_naissance->loadRefOperation();
  }
  $grossesse->getDateAccouchement();
}

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("listSejours", $listSejours);

$smarty->display("inc_tdb_hospitalisations.tpl");
