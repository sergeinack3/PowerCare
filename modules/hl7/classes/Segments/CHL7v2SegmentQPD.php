<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\FieldSpecs\CBirthDateSpec;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentQPD
 * QPD - Represents an HL7 QPD message segment (Query Parameter Definition)
 */

class CHL7v2SegmentQPD extends CHL7v2Segment {
  public $name   = "QPD";


  /** @var CPatient */
  public $patient;


  /** @var CSejour */
  public $sejour;


  /** @var CAffectation */
  public $affectation;

  /**
   * Build QPD segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    if ($event->code == "Q22" || $event->code == "ZV1") {
      $data = $this->buildIHEPDQQuery();
    }
    elseif ($event->code == "Q23") {
      $data = $this->buildIHEPIXQuery();
    }
    else {
      // QPD-1 : Message Query Name (CE)
      $data[] = null;

      // QPD-2 : Query Tag (ST)
      $data[] = null;

      // QPD-3 : User Parameters (in successive fields) (Varies) (QIP)
      $data[] = null;

      // QPD-4 : Search Confidence Threshold (NM)
      $data[] = null;

      // QPD-5 : Algorithm Name (ST)
      $data[] = null;

      // QPD-6 : Algorithm Version (ST)
      $data[] = null;

      // QPD-7 : Algorithm Description (ST)
      $data[] = null;

      // QPD-8 : What domains returned (CX)
      $data[] = null;
    }

    $this->fill($data);
  }

  /**
   * Build PDQ query
   *
   * @return array $data
   */
  function buildIHEPDQQuery() {
    $patient = $this->patient;
    $sejour  = $this->sejour;

    // QPD-1: Message Query Name (CE)
    $data[] = "IHE PDQ Query";

    $QPD2 = null;
    if (isset($patient->_query_tag) && $patient->_query_tag) {
      $QPD2 = $patient->_query_tag;
    }
    else {
      $QPD2 = str_replace(".", "", uniqid("", true));
    }

    // QPD-2: Query Tag (ST)
    $data[] = $QPD2;

    // QPD-3: User Parameters (in successive fields) (Varies) (QIP)
    $QPD3 = array();
    // PID
    if ($patient) {
      $QPD3 = array_merge($QPD3, $this->addQPD3PID($patient));
    }
    // PV1
    if ($sejour) {
      $QPD3 = array_merge($QPD3, $this->addQPD3PV1($sejour));
    }
    CMbArray::removeValue("", $QPD3);
    $data[] = $QPD3;

    // QPD-4 : Search Confidence Threshold (NM)
    $data[] = null;

    // QPD-5 : Algorithm Name (ST)
    $data[] = null;

    // QPD-6 : Algorithm Version (ST)
    $data[] = null;

    // QPD-7 : Algorithm Description (ST)
    $data[] = null;

    // QPD-8 : What domains returned (CX)
    $data[] = $this->addQPD8($patient);

    return $data;
  }

  /**
   * Build PIX query
   *
   * @return null
   */
  function buildIHEPIXQuery() {
    $patient = $this->patient;

    // QPD-1 : Message Query Name (CE)
    $data[] = "IHE PIX Query";

    $QPD2 = null;
    if (isset($patient->_query_tag) && $patient->_query_tag) {
      $QPD2 = $patient->_query_tag;
    }
    else {
      $QPD2 = str_replace(".", "", uniqid("", true));
    }

    // QPD-2: Query Tag (ST)
    $data[] = $QPD2;

    // QPD-3 : User Parameters (in successive fields) (Varies) (QIP)
    if (isset($patient->_patient_identifier_list)) {
      $patient_identifier_list = $patient->_patient_identifier_list;

      $data[] = array(
        array(
          CMbArray::get($patient_identifier_list, "person_id_number"),
          null,
          null,
          array(
            CMbArray::get($patient_identifier_list, "person_namespace_id"),
            CMbArray::get($patient_identifier_list, "person_universal_id"),
            CMbArray::get($patient_identifier_list, "person_universal_id_type")
          )
        )
      );
    }
    else {
      $data[] = null;
    }

    // QPD-4 : What domains returned (CX)
    $data[] = $this->addQPD8($patient);

    return $data;
  }

  /**
   * Add PID in QPD segment
   *
   * @param CPatient $patient Person
   *
   * @return array
   */
  function addQPD3PID(CPatient $patient) {
    $qpd3pid = array();

    // PID-3 : Patient Identifier List
    if (isset($patient->_patient_identifier_list)) {
      $patient_identifier_list = $patient->_patient_identifier_list;

      $qpd3pid = array_merge(
        $qpd3pid, array(
          $this->setDemographicsValues($patient, CMbArray::get($patient_identifier_list, "person_id_number")           , "3.1"),
          $this->setDemographicsValues($patient, CMbArray::get($patient_identifier_list, "person_namespace_id")        , "3.4.1"),
          $this->setDemographicsValues($patient, CMbArray::get($patient_identifier_list, "person_universal_id")        , "3.4.2"),
          $this->setDemographicsValues($patient, CMbArray::get($patient_identifier_list, "person_universal_id_type")   , "3.4.3"),
          $this->setDemographicsValues($patient, CMbArray::get($patient_identifier_list, "person_identifier_type_code"), "3.5")
        )
      );
    }

    return array_merge(
      $qpd3pid, array(
        // PID-5 : Patient Name
        $this->setDemographicsFields($patient, "nom", "5.1.1"),
        $this->setDemographicsFields($patient, "prenom", "5.2"),

        // PID-6 : Maiden name
        $this->setDemographicsFields($patient, "nom_jeune_fille", "6.1.1"),

        // PID-7 : Date of birth
        $this->setDemographicsFields($patient, "naissance", "7.1", null, true),

        // PID-8: Administrative Sex
        $this->setDemographicsFields($patient, "sexe", "8", "1"),

        // PID-11 : Patient Adress
        $this->setDemographicsFields($patient, "adresse", "11.1.1"),
        $this->setDemographicsFields($patient, "ville", "11.3"),
        $this->setDemographicsFields($patient, "cp", "11.5"),

        // PID-13 : Phone Number
        // $this->setDemographicsValues($patient, "", "13.6"),
        //  $this->setDemographicsValues($patient, "", "13.7"),
      )
    );
  }

  /**
   * Add PV1 in QPD segment
   *
   * @param CSejour $sejour Visit
   *
   * @return array
   */
  function addQPD3PV1(CSejour $sejour) {
    $qpd3pid = array();

    $sejour->type = $sejour->_admission;

    // PID-3 : Patient Identifier List
    if (isset($sejour->_sejour_identifier_list)) {
      $sejour_identifier_list = $sejour->_sejour_identifier_list;

      $qpd3pid = array_merge(
        $qpd3pid, array(
          $this->setDemographicsValues($sejour, CMbArray::get($sejour_identifier_list, "admit_id_number")           , "18.1"),
          $this->setDemographicsValues($sejour, CMbArray::get($sejour_identifier_list, "admit_namespace_id")        , "18.4.1"),
          $this->setDemographicsValues($sejour, CMbArray::get($sejour_identifier_list, "admit_universal_id")        , "18.4.2"),
          $this->setDemographicsValues($sejour, CMbArray::get($sejour_identifier_list, "admit_universal_id_type")   , "18.4.3"),
          $this->setDemographicsValues($sejour, CMbArray::get($sejour_identifier_list, "admit_identifier_type_code"), "18.5")
        )
      );
    }

    return array_merge(
      $qpd3pid, array(
        // Patient class
        $this->setDemographicsFields($sejour, "type", "2.1", "4"),

        // Assigned Patient Location
        $this->setDemographicsValues($sejour, $sejour->_service, "3.1"),
        $this->setDemographicsValues($sejour, $sejour->_chambre, "3.2"),
        $this->setDemographicsValues($sejour, $sejour->_lit    , "3.3"),

        $this->setDemographicsValues($sejour, $sejour->_praticien_attending, "7.2.1"),
        $this->setDemographicsValues($sejour, $sejour->_praticien_referring, "8.2.1"),
        $this->setDemographicsValues($sejour, $sejour->_praticien_admitting, "17.2.1"),
        //$this->setDemographicsValues($sejour, $sejour->_praticien_admitting, "17.2.1"),
      )
    );
  }

  /**
   * Populating QPD-3 demographics fields
   *
   * @param CMbObject $object    Object
   * @param string    $mb_field  Field spec
   * @param string    $hl7_field The number of a field
   * @param null      $mapTo     Map to table HL7
   *
   * @return array
   */
  function setDemographicsFields(CMbObject $object, $mb_field, $hl7_field, $mapTo = null) {
    if (!$object->$mb_field) {
      return;
    }

    $seg = null;
    switch ($object->_class) {
      case "CPatient":
        $seg = "PID";
        break;

      case "CSejour":
        $seg = "PV1";
        break;

      default:
    }

    if (!$seg) {
      return;
    }

    $value = $mapTo ? CHL7v2TableEntry::mapTo($mapTo, $object->$mb_field) : $object->$mb_field;

    $spec = $object->_specs[$mb_field];
    if ($spec instanceof CDateSpec || $spec instanceof CBirthDateSpec) {
      $value = CHL7v2::getDate($value);
    }

    return array(
      "@$seg.$hl7_field",
      $value
    );
  }

  /**
   * Populating QPD-3 demographics value
   *
   * @param CMbObject $object Object
   * @param string    $value  Value
   * @param string    $field  The number of a field
   *
   * @return array
   */
  function setDemographicsValues(CMbObject $object, $value, $field) {
    if (!$value) {
      return;
    }

    $seg = null;
    switch ($object->_class) {
      case "CPatient":
        $seg = "PID";
        break;

      case "CSejour":
        $seg = "PV1";
        break;

      default:
    }

    return array(
      "@$seg.$field",
      $value
    );
  }

  /**
   * Add QPD8 field
   *
   * @param CPatient $patient Person
   *
   * @return array
   */
  function addQPD8(CPatient $patient) {
    if (!isset($patient->_domains_returned)) {
      return null;
    }

    $domains_returned = $patient->_domains_returned;
    $QPD8 = array (
      CMbArray::get($domains_returned, "domains_returned_namespace_id"),
      CMbArray::get($domains_returned, "domains_returned_universal_id"),
      CMbArray::get($domains_returned, "domains_returned_universal_id_type")
    );
    $QPD8_copy = $QPD8;

    CMbArray::removeValue(null, $QPD8_copy);
    if (count($QPD8_copy) == 0) {
      return null;
    }

    if (empty($QPD8)) {
      return null;
    }

    return array(
      array(
        null,
        null,
        null,
        $QPD8
      )
    );
  }
}