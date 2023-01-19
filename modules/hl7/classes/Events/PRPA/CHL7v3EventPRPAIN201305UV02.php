<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CMbArray;
use Ox\Core\CMbSecurity;
use Ox\Interop\Eai\CMbOID;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v3EventPRPAIN201307UV02
 * Patient Registry Get Demographics Query
 */
class CHL7v3EventPRPAIN201305UV02 extends CHL7v3EventPRPA implements CHL7EventPRPAST201317UV02 {

  /** @var string */
  public $interaction_id = "IN201305UV02";
  public $values;

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

    // code
    $code = $dom->addElement($controlActProcess, "code");
    $this->setCode($code, "PRPA_TE201305UV02", "2.16.840.1.113883.1.6", "Recherche de patients");

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

    // livingSubjectAdministrativeGender
    if (CMbArray::get($this->values, "sexe") &&  CMbArray::get($this->values, "sexe") != "none") {
      $livingSubjectAdministrativeGender = $dom->addElement($parameterList, "livingSubjectAdministrativeGender");
      $valueSubjectAdministrativeGender = $dom->addElement($livingSubjectAdministrativeGender, "value");
      $dom->addAttribute($valueSubjectAdministrativeGender, "code", mb_strtoupper(CMbArray::get($this->values, "sexe")));
      $dom->addElement($livingSubjectAdministrativeGender, "semanticsText", "LivingSubject.administrativeGender");
    }

    // livingSubjectBirthTime (format : AAAAMMJJ)
    if (CMbArray::get($this->values, "naissance")) {
      $livingSubjectBirthTime = $dom->addElement($parameterList, "livingSubjectBirthTime");
      $valueSubjectBirthTime = $dom->addElement($livingSubjectBirthTime, "value");
      $dom->addAttribute($valueSubjectBirthTime, "value", str_replace("-", "", CMbArray::get($this->values, "naissance")));
      $dom->addElement($livingSubjectBirthTime, "semanticsText", "LivingSubject.birthTime");
    }

    // livingSubjectName (critère nom/prénom)
    if (CMbArray::get($this->values, "nom_approchant") || CMbArray::get($this->values, "prenom_approchant")
      || CMbArray::get($this->values, "nom") || CMbArray::get($this->values, "prenom")) {
      $livingSubjectName = $dom->addElement($parameterList, "livingSubjectName");
      $value             = $dom->addElement($livingSubjectName, "value");
      if (CMbArray::get($this->values, "nom_approchant") || CMbArray::get($this->values, "prenom_approchant")) {
        $dom->addAttribute($value, "use","SRCH");
      }
      if (CMbArray::get($this->values, "nom")) {
        $family = $dom->addElement($value, "family", CMbArray::get($this->values, "nom"));
      }
      if (CMbArray::get($this->values, "prenom")) {
        $given = $dom->addElement($value, "given", CMbArray::get($this->values, "prenom"));
      }
      $dom->addElement($livingSubjectName, "semanticsText", "LivingSubject.name");
    }

    // patientAddress
    if (CMbArray::get($this->values, "ville_approchant") || CMbArray::get($this->values, "cp") || CMbArray::get($this->values, "ville")) {
      $patientAddress        = $dom->addElement($parameterList, "patientAddress");
      $value_patient_address = $dom->addElement($patientAddress, "value");
      if (CMbArray::get($this->values, "ville_approchant")) {
        $dom->addAttribute($value_patient_address, "use","SRCH");
      }
      if (CMbArray::get($this->values, "cp")) {
        $postal_code = $dom->addElement($value_patient_address, "postalCode", CMbArray::get($this->values, "cp"));
      }
      if (CMbArray::get($this->values, "ville")) {
        $city = $dom->addElement($value_patient_address, "city", CMbArray::get($this->values, "ville"));
      }
      $dom->addElement($patientAddress, "semanticsText", "LivingSubject.addr");
    }
  }
}
