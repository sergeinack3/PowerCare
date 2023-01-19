<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHL7v3AcknowledgmentPRPA;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v3EventPRPAIN201308UV02
 * Patient Registry Get Demographics Query Response
 */
class CHL7v3EventPRPAIN201308UV02 extends CHL7v3AcknowledgmentPRPA implements CHL7EventPRPAST201317UV02 {
  /** @var string */
  public $interaction_id = "IN201308UV02";
  public $queryAck;
  public $subject;

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
    return "{$this->event_type}_{$this->interaction_id}";
  }

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    $dom = $this->dom;

    $this->acknowledgment = $dom->queryNode("//hl7:".$this->getInteractionID()."/hl7:acknowledgement");

    return $dom->getValueAttributNode($this->acknowledgment, "typeCode");
  }

  /**
   * Get query ack
   *
   * @return string
   */
  function getQueryAck() {
    $dom = $this->dom;

    $this->queryAck = $dom->queryNode("//hl7:".$this->getInteractionID()."/hl7:controlActProcess/hl7:queryAck");

    $queryResponseCode = $dom->queryNode("hl7:queryResponseCode", $this->queryAck);

    return $dom->getValueAttributNode($queryResponseCode, "code");
  }

  /**
   * Get status code
   *
   * @return string
   */
  function getStatusCode() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $this->subject = $dom->queryNode("$interaction_id/hl7:controlActProcess/hl7:subject");

    $patient = $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient", $this->subject);

    $statusCode = $dom->queryNode("hl7:statusCode", $patient);

    return $dom->getValueAttributNode($statusCode, "code");
  }

  /**
   * Get Patient with Administrative informations in ack
   *
   * @return array
   */
  function getPatientAdministrativeInformation() {
    $patient = new CPatient();

    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $this->subject = $dom->queryNode("$interaction_id/hl7:controlActProcess/hl7:subject");

    $patient_data = $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient", $this->subject);

    $patient->nom_jeune_fille =
      $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:family[@qualifier='BR']", $patient_data);
    $patient->nom =
      $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:family[@qualifier='SP']", $patient_data);
    $patient->prenom    = $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:given", $patient_data);
    $date = $dom->queryTextNode("hl7:patientPerson/hl7:birthTime/@value", $patient_data);
    $patient->naissance = $date ? CMbDT::date($date) : null;
    $patient->sexe      = strtolower($dom->queryTextNode("hl7:patientPerson/hl7:administrativeGenderCode/@code", $patient_data));
    if ($patient->sexe == 'u') {
      $patient->sexe = null;
    }

    $telecoms = $dom->queryNodes("hl7:patientPerson/hl7:telecom", $patient_data);
    foreach ($telecoms as $_telecom) {
      // tel2 ?
      if (!$patient->tel2 && $dom->queryTextNode("@use", $_telecom) && $dom->queryTextNode("@use", $_telecom) == "MC") {
        $patient->tel2 = str_replace("tel:", "", $dom->queryTextNode("@value", $_telecom));
      }
      // tel ?
      if (!$patient->tel && $dom->queryTextNode("@use", $_telecom) && $dom->queryTextNode("@use", $_telecom) == "HP") {
        $patient->tel = str_replace("tel:", "", $dom->queryTextNode("@value", $_telecom));
      }
      // Mail ?
      if (!$patient->email && preg_match("#mailto#", $dom->queryTextNode("@value", $_telecom))) {
        $patient->email = str_replace("mailto:", "", $dom->queryTextNode("@value", $_telecom));
      }
    }

    return $patient;
  }

  /**
   * Get Administrative informations in ack
   *
   * @return array
   */
  function getAdministrativeInformation() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $this->subject = $dom->queryNode("$interaction_id/hl7:controlActProcess/hl7:subject");

    $patient = $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient", $this->subject);

    $administrative_informations = array();
    $administrative_informations["nom_jeune_fille"] =
      $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:family[@qualifier='BR']", $patient);
    $administrative_informations["nom"] =
      $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:family[@qualifier='SP']", $patient);
    $administrative_informations["prenom"]    = $dom->queryTextNode("hl7:patientPerson/hl7:name/hl7:given", $patient);
    $date = $dom->queryTextNode("hl7:patientPerson/hl7:birthTime/@value", $patient);
    $administrative_informations["naissance"] = $date ? CMbDT::date($date) : null;
    $administrative_informations["sexe"]      = CAppUI::tr("CPatient.sexe.".strtolower($dom->queryTextNode("hl7:patientPerson/hl7:administrativeGenderCode/@code", $patient)));
    $administrative_informations["email"] = null;
    $administrative_informations["tel"] = null;
    $administrative_informations["tel2"] = null;

    $telecoms = $dom->queryNodes("hl7:patientPerson/hl7:telecom", $patient);
    foreach ($telecoms as $_telecom) {
      // tel2 ?
      if (!CMbArray::get($administrative_informations, "tel2") && $dom->queryTextNode("@use", $_telecom) && $dom->queryTextNode("@use", $_telecom) == "MC") {
        $administrative_informations["tel2"] = str_replace("tel:", "", $dom->queryTextNode("@value", $_telecom));
      }
      // tel ?
      if (!CMbArray::get($administrative_informations, "tel") && $dom->queryTextNode("@use", $_telecom) && $dom->queryTextNode("@use", $_telecom) == "HP") {
        $administrative_informations["tel"] = str_replace("tel:", "", $dom->queryTextNode("@value", $_telecom));
      }
      // Mail ?
      if (!CMbArray::get($administrative_informations, "email") && preg_match("#mailto#", $dom->queryTextNode("@value", $_telecom))) {
        $administrative_informations["email"] = str_replace("mailto:", "", $dom->queryTextNode("@value", $_telecom));
      }
    }

    return$administrative_informations;
  }

  /**
   * Get Gestion information in ack
   *
   * @return array
   */
  function getGestionInformation() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $this->subject = $dom->queryNode("$interaction_id/hl7:controlActProcess/hl7:subject");

    $patient = $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient", $this->subject);

    $gestion_informations = array();

    $administrativeObservation = $dom->queryNodes("hl7:subjectOf/hl7:administrativeObservation", $patient);

    foreach ($administrativeObservation as $_administrativeObservation) {
      $gestion_informations[$dom->queryTextNode("hl7:code/@code", $_administrativeObservation)] =
        $dom->queryTextNode("hl7:value/@value", $_administrativeObservation);
    }

    return$gestion_informations;
  }

  /**
   * Get closing date
   *
   * @return string
   */
  function getDateFermeture() {
    $dom = $this->dom;
    $effectiveTime = $dom->queryNode("hl7:registrationEvent/hl7:effectiveTime", $this->subject);
    if (!$effectiveTime) {
      return null;
    }

    return $this->setUtcToTime($dom->getValueAttributNode($effectiveTime, "value"));
  }

  /**
   * Get closing pattern
   * @param bool $only_code retourne la traduction du code par défaut, le code seul si passé à true
   *
   * @return string
   */
  function getMotifFermeture($only_code = false) {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $code = $dom->queryNode($interaction_id."/hl7:controlActProcess/hl7:reasonOf/hl7:detectedIssueEvent/hl7:code");

    $reasonOf = $dom->getValueAttributNode($code, "code");

    $reasonOf = $reasonOf ? $reasonOf : "none";

    return $reasonOf;
  }

  /**
   * Get closing motif text
   *
   * @return string
   */
  function getMotifFermetureText() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $reasonOf = $dom->queryNode($interaction_id."/hl7:controlActProcess/hl7:reasonOf/hl7:detectedIssueEvent");

    $motif = $dom->queryTextNode("hl7:text", $reasonOf);

    return $motif;
  }

  /**
   * Get authorization status
   *
   * @return string
   */
  function getAuthorizationStatus() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $value = $dom->queryNode($interaction_id."/hl7:attentionLine[hl7:keyWordText[@code='AUTORISATION']]/hl7:value");

    return $dom->getValueAttributNode($value, "code");
  }

  /**
   * Get status doctor
   *
   * @return string
   */
  function getStatusMT() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $value = $dom->queryNode($interaction_id."/hl7:attentionLine[hl7:keyWordText[@code='STATUT_MT']]/hl7:value");

    if (!$value) {
      return;
    }

    return $dom->getValueAttributNode($value, "value");
  }
}