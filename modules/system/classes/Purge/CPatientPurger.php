<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Purge;

/**
 * Description
 */
class CPatientPurger extends CObjectPurger {
  protected $class_name = "CPatient";

  /**
   * @inheritdoc
   */
  protected function getLJoin() {
    return array(
      "files_mediboard F ON (F.object_class = 'CPatient' AND F.object_id = `patients`.patient_id)",
      "compte_rendu CR ON (CR.object_class = 'CPatient' AND CR.object_id = `patients`.patient_id)",
      "consultation C ON C.patient_id = `patients`.patient_id",
      "sejour S ON S.patient_id = `patients`.patient_id",
    );
  }

  /**
   * @inheritdoc
   */
  protected function getWhere() {
    return array(
      "F.file_id IS NULL",
      "CR.compte_rendu_id IS NULL",
      "C.consultation_id IS NULL",
      "S.sejour_id IS NULL",
    );
  }
}
