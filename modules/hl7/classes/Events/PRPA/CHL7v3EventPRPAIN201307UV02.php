<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CAppUI;
use Ox\Core\CMbSecurity;
use Ox\Interop\Eai\CMbOID;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v3EventPRPAIN201307UV02
 * Patient Registry Get Demographics Query
 */
class CHL7v3EventPRPAIN201307UV02 extends CHL7v3EventPRPA implements CHL7EventPRPAST201317UV02 {

  /** @var string */
  public $interaction_id = "IN201307UV02";

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
    return "{$this->event_type}_{$this->interaction_id}";
  }

  /**
   * Build IN201307UV02 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
    parent::build($patient);

    $this->dom->dirschemaname = $this->getInteractionID();

    $this->addControlActProcess($patient);

    $this->message = $this->dom->saveXML();

    // Modification de l'échange
    $this->updateExchange();
  }

  /**
   * @see parent::addControlActProcess()
   */
  function addControlActProcess(CPatient $patient) {
    $dom = $this->dom;

    $controlActProcess = parent::addControlActProcess($patient);

    // reasonCode
    $reasonCode = $dom->addElement($controlActProcess, "reasonCode");

    if ($this->_reasonCode) {
      switch ($this->_reasonCode) {
        case 'TEST_EXST':
          $this->setCode($reasonCode, "TEST_EXST", "1.2.250.1.213.1.1.4.11", "Test d'existence de dossier");
          break;

        case 'CNSLT_DATA':
          $this->setCode($reasonCode, "CNSLT_DATA", "1.2.250.1.213.1.1.4.11", "Consultation de données de gestion de dossier");
          break;

        default;
      }
    }
    else {
      $this->setCode($reasonCode, "TEST_EXST", "1.2.250.1.213.1.1.4.11", "Test d'existence de dossier");
    }

    // queryByParameter
    $queryByParameter = $dom->addElement($controlActProcess, "queryByParameter");

    // queryId
    $queryId = $dom->addElement($queryByParameter, "queryId");
    $this->setII($queryId, CMbSecurity::generateUUID(), CMbOID::getOIDFromClass($this->_exchange_hl7v3, $this->_receiver));

    // statusCode
    $statusCode = $dom->addElement($queryByParameter, "statusCode");
    $dom->addAttribute($statusCode, "code", "new");

    // parameterList
    $parameterList = $dom->addElement($queryByParameter, "parameterList");

    // patientIdentifer
    $patientIdentifer = $dom->addElement($parameterList, "patientIdentifier");
    $value = $dom->addElement($patientIdentifer, "value");
    $this->setII($value, $patient->getINSNIR(), CAppUI::conf("dmp NIR_OID"));

    $dom->addElement($patientIdentifer, "semanticsText", "Patient.id");
  }
}
