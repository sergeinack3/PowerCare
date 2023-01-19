<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::check();
$sejour_id = CView::get("sejour_id", "ref class|CSejour default|0", true);
CView::checkin();

// Chargement du sejour
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargement du dossier medical
$sejour->loadRefDossierMedical();
$dossier_medical =& $sejour->_ref_dossier_medical;
$dossier_medical->needsRead();

// Chargement des antecedents et traitements
//absence atcd
$atcd_absence = $dossier_medical->loadRefsAntecedents(true, false, false, false, 1);

//atcd
$dossier_medical->loadRefsAntecedents(true);
if ($dossier_medical->_ref_antecedents_by_type) {
  $dossier_medical->countAntecedents();
  $dossier_medical->countTraitements();
  foreach ($dossier_medical->_ref_antecedents_by_type as &$type) {
    foreach ($type as $_ant) {
      $_ant->updateOwnerAndDates();
    }
  }
}

$dossier_medical->loadRefsTraitements(true);

// Chargement de la prescription de sejour
$prescription = $sejour->loadRefPrescriptionSejour();

// Chargement des lignes de tp de la prescription
/** @var CPrescriptionLineMedicament[]  $lines_tp */
$lines_tp = array();
if ($prescription && $prescription->_id && CPrescription::isMPMActive()) {
  $line_tp = new CPrescriptionLineMedicament();
  $line_tp->prescription_id = $prescription->_id;
  $line_tp->traitement_personnel = 1;
  $lines_tp = $line_tp->loadMatchingList();

  foreach ($lines_tp as $_line_tp) {
    $_line_tp->loadRefsPrises();
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour"      , $sejour);
$smarty->assign("lines_tp"    , $lines_tp);
$smarty->assign("atcd_absence", $atcd_absence);
$smarty->display("inc_list_ant_anesth.tpl");
