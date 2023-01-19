<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

global $m;

CCanDo::checkRead();
$sejour_id = CValue::getOrSession("sejour_id");

// Chargement du sejour
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$prescription = $sejour->loadRefPrescriptionSejour();

// Prescription peut ne pas être actif
if ($prescription) {
  $prescription->countBackRefs("prescription_line_element");
}

$group_id = CGroups::loadCurrent()->_id;
// Recherche des sejours SSR du patient
$where               = array();
$where["patient_id"] = " = '$sejour->patient_id'";
$where["type"]       = " = '$m'";
$where["annule"]     = " = '0'";
$where["sejour_id"]  = " != '$sejour->_id'";
$where["sortie"]     = " <= '$sejour->entree'";
$where["group_id"]   = " = '$group_id'";

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where);
foreach ($sejours as $_sejour) {
  $_sejour->loadRefBilanSSR()->loadRefPraticienDemandeur();
  $_sejour->loadRefPraticien(1);

  $prescription = $_sejour->loadRefPrescriptionSejour();
  $prescription->loadRefsLinesElementByCat();
  $prescription->countBackRefs("prescription_line_element");
  foreach ($prescription->_ref_prescription_lines_element_by_cat as $_lines_by_chap) {
    foreach ($_lines_by_chap as $_lines_by_cat) {
      foreach ($_lines_by_cat as $_lines_by_elt) {
        foreach ($_lines_by_elt as $_line) {
          /* @var CPrescriptionLineElement $_line */
          $_line->getRecentModification();
        }
      }
    }
  }
}

$colors = CColorLibelleSejour::loadAllFor(CMbArray::pluck($sejours, "libelle"));

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("sejour", $sejour);
$smarty->assign("sejours", $sejours);
$smarty->assign("colors", $colors);
$smarty->display("inc_vw_sejours_patient");
