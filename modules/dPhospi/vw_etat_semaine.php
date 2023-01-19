<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$type_admission = CValue::getOrSession("type_admission", "ambucomp");

// Liste des chirurgiens
$listChirs = array();
$listPats  = array();

// Récupération des admissions à affecter
function loadSejourNonAffectes($where) {
  global $listChirs, $listPats, $listFunctions;

  $group = CGroups::loadCurrent();

  $leftjoin                 = array(
    "affectation" => "sejour.sejour_id = affectation.sejour_id"
  );
  $where["sejour.group_id"] = "= '$group->_id'";
  $where[]                  = "affectation.affectation_id IS NULL";

  $sejourNonAffectes = new CSejour;
  $sejourNonAffectes = $sejourNonAffectes->loadList($where, null, null, null, $leftjoin);

  foreach ($sejourNonAffectes as $keySejour => $valSejour) {
    $sejour =& $sejourNonAffectes[$keySejour];
  }

  return $sejourNonAffectes;
}

$today = CMbDT::date() . " 01:00:00";
$to    = CMbDT::dateTime("-1 second", $today);
$list  = array();
for ($i = 1; $i <= 7; $i++) {
  $from            = CMbDT::dateTime("+1 second", $to);
  $to              = CMbDT::dateTime("+1 day", $to);
  $where           = array();
  $where["annule"] = "= '0'";
  switch ($type_admission) {
    case "ambucomp":
      $where[] = "sejour.type = 'ambu' OR sejour.type = 'comp'";
      break;
    case "ambucompssr":
      $where[] = "sejour.type = 'ambu' OR sejour.type = 'comp' OR sejour.type = 'ssr'";
      break;
    case "0":
      break;
    default:
      $where["sejour.type"] = "= '$type_admission'";
  }
  $where["sejour.entree"] = "BETWEEN '$from' AND '$to'";
  $list[$from]            = loadSejourNonAffectes($where);
}

// Création du template
$smarty = new CSmartyDP();


$smarty->assign("list", $list);
$smarty->assign("type_admission", $type_admission);

$smarty->display("vw_etat_semaine.tpl");

