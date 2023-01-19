<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Tooltip des antécédents du patient
 */
CCanDo::checkRead();

$dossier_medical_id = CView::get("dossier_medical_id", "ref class|CDossierMedical");
$object_guid        = CView::get("object_guid", "str");
$type               = CView::get("type", "str");

CView::checkin();

if ($object_guid) {
  $dossier_medical = CMbObject::loadFromGuid($object_guid);
}
else {
  $dossier_medical = new CDossierMedical();
  $dossier_medical->load($dossier_medical_id);
}

$atcds = array();
if ($type) {
  $atcds = $dossier_medical->loadRefsAntecedentsOfType($type);
}
else {
  $atcds = $dossier_medical->loadRefsAntecedents(false, false, true);
}

CStoredObject::massLoadBackRefs($atcds, "hypertext_links");
foreach ($atcds as $_atcd) {
  $_atcd->loadRefsHyperTextLink();
}

$tab_atc     = array();
$ant_communs = array();

$patient = null;
if ($dossier_medical->object_class == "CSejour") {
  $dossier_medical->loadRefObject();
  /* @var CSejour $sejour */
  $sejour       = $dossier_medical->_ref_object;
  $doss_patient = $sejour->loadRefPatient()->loadRefDossierMedical();

  $atcds = array();
  if ($type) {
    $atcds = $doss_patient->loadRefsAntecedentsOfType($type);
  }
  else {
    $atcds = $doss_patient->loadRefsAntecedents(false, false, true);
  }

  CStoredObject::massLoadBackRefs($atcds, "hypertext_links");
  foreach ($atcds as $_atcd) {
    $_atcd->loadRefsHyperTextLink();
  }

  $tab_atc["CPatient"] = $doss_patient->_ref_antecedents_by_type;
  $tab_atc["CSejour"]  = $dossier_medical->_ref_antecedents_by_type;

  foreach ($tab_atc["CSejour"] as $type => $ant_sej_type) {
    foreach ($ant_sej_type as $ant_id => $ant_sej) {
      if (isset($tab_atc["CPatient"][$type])) {
        foreach ($tab_atc["CPatient"][$type] as $ant_pat_id => $ant_pat) {
          if ($ant_pat->appareil == $ant_sej->appareil && $ant_pat->date == $ant_sej->date && $ant_pat->rques == $ant_sej->rques && $ant_pat->annule == $ant_sej->annule) {
            unset($tab_atc["CSejour"][$type][$ant_id]);
            unset($tab_atc["CPatient"][$type][$ant_pat_id]);
            $ant_communs[$type][] = $ant_pat;
          }
        }
      }
    }
    if (!count($tab_atc["CSejour"][$type]) || $type == "alle") {
      unset($tab_atc["CSejour"][$type]);
    }
  }
}
else {
  $tab_atc[$dossier_medical->object_class] = $dossier_medical->_ref_antecedents_by_type;
}

$smarty = new CSmartyDP();

$smarty->assign("tab_atc", $tab_atc);
$smarty->assign("ant_communs", $ant_communs);
$smarty->assign("type", $type);
$smarty->assign("patient", $patient);

$smarty->display("inc_tooltip_atcd.tpl");