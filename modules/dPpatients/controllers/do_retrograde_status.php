<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSourceIdentite;

CCanDo::checkEdit();

$patient_id = CView::postRefCheckEdit('patient_id', 'ref class|CPatient');

CView::checkin();

$patient = CPatient::findOrFail($patient_id);

// Désactivation de la source actuelle
$actual_source = $patient->loadRefSourceIdentite();

$actual_source->active = 0;

CSourceIdentite::$update_patient_status = false;
$msg = $actual_source->store();
CSourceIdentite::$update_patient_status = true;

CAppUI::setMsg($msg ?: 'CSourceIdentite-msg-modify', $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Recherche d'une nouvelle source
$new_source = new CSourceIdentite();

$ds = $new_source->getDS();

$where = [
    'patient_id' => $ds->prepare('= ?', $patient->_id),
    'active'     => "= '1'"
];

// Avec justificatif si la source actuelle était insi
if ($actual_source->getModeObtention() === CSourceIdentite::MODE_OBTENTION_INSI) {
    $where['identity_proof_type_id'] = 'IS NOT NULL';
}

$new_source->loadObject($where);

$patient->source_identite_id = $new_source->_id;
$patient->loadRefSourceIdentite(false);

$msg = $patient->store();

CAppUI::setMsg($msg ?: 'CPatient-msg-modify', $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();
