<?php

/**
 * $Id$
 *  
 * @category hl7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  OXOL, see http://www.mediboard.org/public/OXOL
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

namespace Ox\Interop\Hl7\Events\PRPA;

use Ox\Core\CAppUI;
use Ox\Interop\Hl7\CHL7v3AcknowledgmentPRPA;

/**
 * Class CHL7v3EventPRPAIN201308UV02
 * Find Candidates Query Response
 */
class CHL7v3EventPRPAIN201306UV02 extends CHL7v3AcknowledgmentPRPA implements CHL7EventPRPAST201317UV02 {
  /** @var string */
  public $interaction_id = "IN201306UV02";
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

    $this->acknowledgment = $dom->queryNode("//hl7:".$this->getInteractionID()."/hl7:acknowledgement/hl7:typeCode");

    return $dom->getValueAttributNode($this->acknowledgment, "code");
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

    if (!$queryResponseCode) {
      return null;
    }

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
   * Get number of patients found with find candidates query
   *
   * @return int
   */
  function resultTotalQuantity() {
    $dom = $this->dom;

    $this->queryAck = $dom->queryNode("//hl7:".$this->getInteractionID()."/hl7:controlActProcess/hl7:queryAck");

    $resultTotalQuantity = $dom->getValueAttributNode($dom->queryNode("hl7:resultTotalQuantity", $this->queryAck), "value");

    return $resultTotalQuantity;
  }

  /**
   * Get informations about patients found with find candidates query
   *
   * @return array
   */
  function getPatientsFound() {
    $dom = $this->dom;

    $interaction_id = "//hl7:".$this->getInteractionID();

    $patients = $dom->queryNodes("$interaction_id/hl7:controlActProcess/hl7:subject");

    $patients_found = array();
    foreach ($patients as $_patient) {
      $patientPerson = $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient/hl7:patientPerson", $_patient);
      $NIR_OID = CAppUI::conf("dmp NIR_OID");
      $insc_patient  = $dom->getValueAttributNode(
        $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient/hl7:id[@root='$NIR_OID']", $_patient), "extension"
      );

      if (!$insc_patient) {
        continue;
      }

      $matricule = $dom->getValueAttributNode(
        $dom->queryNode("hl7:registrationEvent/hl7:subject1/hl7:patient/hl7:id[@root='1.2.250.1.213.1.4.2']", $_patient), "extension"
      );

      $patients_found[$matricule]["prenom"]          = $dom->queryTextNode("hl7:name/hl7:given", $patientPerson);
      $patients_found[$matricule]["nom"]             = $dom->queryTextNode("hl7:name/hl7:family[@qualifier='SP']", $patientPerson);
      $patients_found[$matricule]["nom_jeune_fille"] = $dom->queryTextNode("hl7:name/hl7:family[@qualifier='BR']", $patientPerson);
      $patients_found[$matricule]["naissance"]       = $dom->getValueAttributNode(
        $dom->queryNode("hl7:birthTime", $patientPerson), "value"
      );
      $patients_found[$matricule]["ville"] = $dom->queryTextNode("hl7:addr/hl7:city", $patientPerson);
      $patients_found[$matricule]["cp"] = $dom->queryTextNode("hl7:addr/hl7:postalCode", $patientPerson);
    }

    return $patients_found;
  }
}
