<?php

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
class CDoPatientAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CPatient", "patient_id");

    if ($dialog = CValue::post("dialog")) {
      $this->redirectDelete .= $this->redirect . "&a=pat_selector&dialog=1";
    }
    else {
      $tab                  = CValue::post("tab", "vw_edit_patients");
      $this->redirectDelete .= $this->redirect . "&tab=$tab";
    }
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    parent::doStore();

    $dialog = CValue::post("dialog");
      if ($dialog) {
          $this->redirect .= "&a=vw_edit_patients&dialog=1&patient_id=" . $this->_obj->patient_id;

          if (CAppUI::gconf("dPpatients CPatient auto_selected_patient")) {
              $this->redirectStore .= "&patient_id=" . $this->_obj->patient_id;
          }
      }
    else {
      $this->redirectStore .= "&m=patients&tab=vw_idx_patients&id=" . $this->_obj->patient_id;
    }
  }

  /**
   * @inheritdoc
   */
  function doDelete() {
    parent::doDelete();

    $dialog = CValue::post("dialog");
    if ($dialog) {
      $this->redirectDelete .= "&name=" . $this->_obj->nom . "&firstName=" . $this->_obj->prenom . "&id=0";
    }
  }
}

$do = new CDoPatientAddEdit();
$do->doIt();
