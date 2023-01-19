<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CAppUI;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v3EventPRPAIN201314UV02
 * Patient Registry Request Add Patient
 */
class CHL7v3EventPRPAIN201314UV02 extends CHL7v3EventPRPA implements CHL7EventPRPAST201317UV02 {
  /** @var string */
  public $interaction_id = "IN201314UV02";
  public $queryAck;
  public $subject;

  public $_reasonCode;

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
    return "{$this->event_type}_{$this->interaction_id}";
  }

  /**
   * Build IN201314UV02 event
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
    $transform_date = false;
    $controlActProcess = parent::addControlActProcess($patient);

    // reasonCode
    $reasonCode = $dom->addElement($controlActProcess, "reasonCode");
    switch ($this->_reasonCode) {
      case 'REAC':
        $this->setCode($reasonCode, "REAC", "1.2.250.1.213.1.1.4.11", "Réactivation de dossier");
        $patient->_dmp_reactivation_dmp = true;
        break;

      case 'FERM':
        $this->setCode($reasonCode, "FERM", "1.2.250.1.213.1.1.4.11", "Fermeture de dossier");
        $patient->_dmp_reactivation_dmp = true;
        break;

      case 'MODIF_DATA':
        $this->setCode($reasonCode, "MODIF_DATA", "1.2.250.1.213.1.1.4.11", "Modification des données de gestion du dossier");
        $transform_date = true;
        break;

      default;
    }

    // subject
    $subject = $dom->addElement($controlActProcess, "subject");
    $dom->addAttribute($subject, "contextConductionInd", "false");
    $this->setTypeCode($subject, "SUBJ");

    // registrationRequest
    $registrationRequest = $dom->addElement($subject, "registrationRequest");
    $this->setClassMoodCode($registrationRequest, "REG", "RQO");

    // statusCode
    $statusCode = $dom->addElement($registrationRequest, "statusCode");
    $dom->addAttribute($statusCode, "code", $this->_reasonCode == "FERM" ? "terminated" : "active");

    // subject1
    $subject1 = $dom->addElement($registrationRequest, "subject1");
    $this->setTypeCode($subject1, "SBJ");

    // patient
    $patient_dom = $dom->addElement($subject1, "patient");
    $this->setClassCode($patient_dom, "PAT");

    $id = $dom->addElement($patient_dom, "id");
    $this->setII($id, $patient->getINSNIR(), CAppUI::conf("dmp NIR_OID"));

    $statusCode = $dom->addElement($patient_dom, "statusCode");
    $dom->addAttribute($statusCode, "code", $this->_reasonCode == "FERM" ? "terminated" : "active");

    // patientPerson
    $patientPerson = $dom->addElement($patient_dom, "patientPerson");
    $this->setClassDeterminerCode($patientPerson, "PSN", "INSTANCE");

    $this->addName($patientPerson, $patient);

    $this->addTelecom($patientPerson, $patient);

    $administrativeGenderCode = $dom->addElement($patientPerson, "administrativeGenderCode");
    $dom->addAttribute($administrativeGenderCode, "code", strtoupper($patient->sexe));

    $birthTime = $dom->addElement($patientPerson, "birthTime");
    $date = $this->getDateToFormatCDA($patient->_p_birth_date, $transform_date);
    $dom->addAttribute($birthTime, "value", $date);

    $this->addAdress($patientPerson, $patient);

    // Ajout du représentant légal (patient mineur ou tutelle/curatelle)
    if ($patient->tutelle != "aucune" || $patient->_age < 18) {
      $this->addPersonalRelationship($patientPerson, $patient);
    }

    $this->addBirthPlace($patientPerson, $patient);

    // subjectOf
    $dmp_reactivation_dmp = $patient->_dmp_reactivation_dmp ? "true" : "false";
    if ($this->_reasonCode != "FERM") {
      $this->addSubjectOf(
        $patient_dom, "CONSENTEMENT_REACTIVATION_DMP", "1.2.250.1.213.4.1.2.3", "Consentement à la réactivation du DMP",
        $dmp_reactivation_dmp, true
      );
    }

    // author
    $author = $dom->addElement($registrationRequest, "author");
    $this->setTypeCode($author, "AUT");

    // assignedEntity
    $assignedEntity = $dom->addElement($author, "assignedEntity");
    $this->setClassCode($assignedEntity, "ASSIGNED");

    $id = $dom->addElement($assignedEntity, "id");
    $mediuser = $patient->_dmp_mediuser;
    $this->setII($id, $mediuser->_common_name, "1.2.250.1.71.4.2.1");

    // assignedPerson
    $assignedPerson = $dom->addElement($assignedEntity, "assignedPerson");
    $this->setClassDeterminerCode($assignedPerson, "PSN", "INSTANCE");

    $name = $dom->addElement($assignedPerson, "name");
    $name->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $name->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:type', 'PN');
    $dom->addElement($name, "family", $mediuser->_p_last_name);
    $dom->addElement($name, "given", $mediuser->_p_first_name);

    // representedOrganization
    $group = $mediuser->loadRefFunction()->loadRefGroup();
    $this->addRepresentedOrganizationGroup($assignedEntity, $group);

    if ($this->_reasonCode == "FERM") {
      $reasonOf = $dom->addElement($controlActProcess, "reasonOf");
      $this->setTypeCode($reasonOf, "RSON");

      $detectedIssueEvent = $dom->addElement($reasonOf, "detectedIssueEvent");
      $this->setClassMoodCode($detectedIssueEvent, "ALRT", "EVN");

      $code = $dom->addElement($detectedIssueEvent, "code");
      $this->setCode($code, "FERMETURE_DEMANDE_PATIENT", "1.2.250.1.213.4.1.2.4");

      $dom->addElement($detectedIssueEvent, "text", $patient->_dmp_reason_close);
    }

    // Traitement final
    //$dom->purgeEmptyElements();
  }
}