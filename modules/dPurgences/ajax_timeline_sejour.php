<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadSuiviMedical();
$sejour->loadRefPraticien();
$sejour->loadRefConstantes();

// Ajout du RPU
$rpu = $sejour->loadRefRPU();

$rpu_key = $sejour->entree . $rpu->_class;

$sejour->_ref_suivi_medical[$rpu_key] = $rpu;

ksort($sejour->_ref_suivi_medical);

$mapping_datetime = array();

foreach (array_keys($sejour->_ref_suivi_medical) as $_mix_datetime) {
  preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $_mix_datetime, $result);
  $mapping_datetime[$_mix_datetime] = $result[0];
}

foreach ($sejour->_ref_suivi_medical as $_suivi) {
  if ($_suivi instanceof CConsultation) {
    $_suivi->loadRefPraticien();
  }
}

$fields_display = array(
  "CConsultation" => array(
    "motif",
    "histoire_maladie",
    "examen",
    "rques",
    "projet_soins",
    "conclusion"
  ),
  "CRPU"          => array(
    "ide_responsable_id",
    "ioa_id",
    "circonstance",
    "motif_sfmu",
    "diag_infirmier",
    "pec_douleur",
  )
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("mapping_datetime", $mapping_datetime);
$smarty->assign("fields_display", $fields_display);

$smarty->display("inc_timeline_sejour");