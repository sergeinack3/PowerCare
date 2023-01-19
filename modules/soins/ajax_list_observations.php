<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id       = CView::get("sejour_id", "ref class|CSejour");
$function_id     = CView::get('function_id', 'ref class|CFunctions');
$other_sejour_id = CView::get("other_sejour_id", "str");
$type            = CView::get("type", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargements des séjours du patient
$sejour_context             = new CSejour();
$sejour_context->patient_id = $sejour->patient_id;
$sejour_context->annule     = 0;

$sejours_context = $sejour_context->loadMatchingList("entree ASC");

$current_group_id = CGroups::get()->_id;
/** @var CSejour $_sejour */
foreach ($sejours_context as $_sejour) {
  $sharing_files = CAppUI::gconf('dPpatients sharing multi_group', $_sejour->group_id);
  if ($sharing_files !== 'full' && $_sejour->group_id !== $current_group_id) {
    unset($sejours_context[$_sejour->_id]);
    continue;
  }
  $_sejour->loadRefPatient();
}

$observations        = [];
$observations_entree = [];
$functions           = [];

switch ($other_sejour_id) {
  case "all":
    foreach ($sejours_context as $_sejour) {
      $observations = array_merge($observations, $_sejour->loadRefsObservations(null, $type, null, $function_id, $functions));
      // Récupération de l'observation d'entrée
      $obs_entree = $_sejour->loadRefObsEntree();
      if ($obs_entree->_id) {
        $observations_entree[] = $obs_entree;
      }
      $consultations = $_sejour->loadRefsConsultations();
      if ($obs_entree->_id && isset($consultations[$obs_entree->_id])) {
        unset($consultations[$obs_entree->_id]);
      }
      foreach ($consultations as $_consultation) {
        $observations_entree[] = $_consultation;
      }
    }

    $sejour->_ref_observations = $observations;
    break;
  default:
    if ($other_sejour_id) {
      // Cas du changement de patient
      if (isset($sejours_context[$other_sejour_id])) {
        $sejour->load($other_sejour_id);
      }
      else {
        $other_sejour_id = null;
      }
    }

    $observations = $sejour->loadRefsObservations(null, $type, null, $function_id, $functions);

    // Récupération de l'observation d'entrée
    $obs_entree = $sejour->loadRefObsEntree();
    if ($obs_entree->_id) {
      $observations_entree[] = $obs_entree;
    }

    $consultations = $sejour->loadRefsConsultations();
    if ($obs_entree->_id && isset($consultations[$obs_entree->_id])) {
      unset($consultations[$obs_entree->_id]);
    }
    foreach ($consultations as $_consultation) {
      $observations_entree[] = $_consultation;
    }

    if ($other_sejour_id) {
      $sejour->load($sejour_id);
    }
}

CMbArray::pluckSort($observations, SORT_DESC, "date");

CStoredObject::massLoadFwdRef($observations, "user_id");
CStoredObject::massLoadFwdRef($observations, "object_id");

foreach ($observations as $_obs) {
  $_obs->loadRefUser();
  $_obs->canEdit();
  $_obs->loadTargetObject();
}

// Création de la liste des observations + l'observation d'entrée
$list_observations = array();
foreach ($observations as $_observation) {
  $list_observations[$_observation->date . $_observation->_id . "obs"] = $_observation;
}
if (!empty($observations_entree)) {
  foreach ($observations_entree as $_obs_entree) {
    $_obs_entree->canEdit();
    $_obs_entree->loadRefPlageConsult();
    $_obs_entree->loadRefPraticien()->loadRefFunction();
    if ($_obs_entree instanceof CConsultation) {
        $_obs_entree->loadRefSuiviGrossesse();
    }

    foreach ($_obs_entree->loadRefsDossiersAnesth() as $_dossier_anesth) {
        $_dossier_anesth->loadRefOperation()->loadRefPlageOp();
    }
    $list_observations[$_obs_entree->_datetime . $_obs_entree->_guid] = $_obs_entree;
  }
}

krsort($list_observations);

$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("type", $type);
$smarty->assign("sejours_context", $sejours_context);
$smarty->assign("other_sejour_id", $other_sejour_id);
$smarty->assign("list_observations", $list_observations);
$smarty->assign('function_id', $function_id);
$smarty->assign('functions', $functions);

$smarty->display("inc_list_observations");
