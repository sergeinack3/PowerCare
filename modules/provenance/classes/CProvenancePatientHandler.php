<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CProvenancePatientHandler
 *
 * @package Ox\Mediboard\Provenance
 */
class CProvenancePatientHandler extends ObjectHandler {
  /**
   * Classe valide pour le handler
   *
   * @var array
   */
  static $handled = ["CPatient"];

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    if (!CModule::getActive("provenance")) {
      return null;
    }

    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return;
    }
    if ($object instanceof CPatient) {
      if ($object->_provenance_id !== "0") {
        // Chargement de la provenance patient et création/modification
        $object->loadRefProvenancePatient();
        $prov_patient = $object->_ref_provenance_patient;
        $prov_patient->provenance_id   = $object->_provenance_id;
        $prov_patient->patient_id      = $object->_id;
        $prov_patient->commentaire     = $object->_commentaire_prov;
        $prov_patient->store();
      }
      else {
        // Suppression de la provenance patient
        $object->loadRefProvenancePatient();
        if ($object->_ref_provenance_patient->_id !== $object->_provenance_id) {
          $object->_ref_provenance_patient->delete();
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    $this->onAfterStore($object);
  }
}
