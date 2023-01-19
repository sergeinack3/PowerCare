<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$objects_id = CValue::post('objects_id');
if (!is_array($objects_id)) {
  $objects_id = explode("-", $objects_id);
}

if (count($objects_id) != 2) {
  CAppUI::stepAjax("Trop d'objets pour réaliser une association", UI_MSG_ERROR);
}

if (class_exists("CPatient") && count($objects_id)) {
  $patient1 = new CPatient();
  $patient2 = new CPatient();

  if (!$patient1->load($objects_id[0]) || !$patient2->load($objects_id[1])) {
    CAppUI::stepAjax("Chargement impossible du patient", UI_MSG_ERROR);
  }

  $patient1->_doubloon_ids = array($patient2->_id);

  if ($msg = $patient1->store()) {
    CAppUI::stepAjax("Association du patient impossible : $msg", UI_MSG_ERROR);
  }

  CAppUI::stepAjax("$patient1->_view associé avec $patient2->_view");
}

CApp::rip();
