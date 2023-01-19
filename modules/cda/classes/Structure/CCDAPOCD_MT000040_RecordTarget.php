<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_RecordTarget Class
 */
class CCDAPOCD_MT000040_RecordTarget extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_PatientRole
   */
  public $patientRole;

  /**
   * Setter patientRole
   *
   * @param CCDAPOCD_MT000040_PatientRole $inst CCDAPOCD_MT000040_PatientRole
   *
   * @return void
   */
  function setPatientRole($inst = null) {
    $this->patientRole = $inst;
  }

  /**
   * Getter patientRole
   *
   * @return CCDAPOCD_MT000040_PatientRole
   */
  function getPatientRole() {
    return $this->patientRole;
  }

  /**
   * Assigne typeCode à RCT
   *
   * @return void
   */
  function setTypeCode() {
    $partType = new CCDAParticipationType();
    $partType->setData("RCT");
    $this->typeCode = $partType;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAParticipationType
   */
  function getTypeCode() {
    return $this->typeCode;
  }

  /**
   * Assigne contextControlCode à OP
   *
   * @return void
   */
  function setContextControlCode() {
    $context = new CCDAContextControl();
    $context->setData("OP");
    $this->contextControlCode = $context;
  }

  /**
   * Getter contextControlCode
   *
   * @return CCDAContextControl
   */
  function getContextControlCode() {
    return $this->contextControlCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]             = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["patientRole"]        = "CCDAPOCD_MT000040_PatientRole xml|element required";
    $props["typeCode"]           = "CCDAParticipationType xml|attribute fixed|RCT";
    $props["contextControlCode"] = "CCDAContextControl xml|attribute fixed|OP";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = parent::test();

    /**
     * Test avec un patientRole correct
     */

    $rolePatient = new CCDAPOCD_MT000040_PatientRole();
    $ii = new CCDAII();
    $ii->setRoot("1.2.250.1.213.1.1.9");
    $rolePatient->appendId($ii);
    $this->setPatientRole($rolePatient);
    $tabTest[] = $this->sample("Test avec un patientRole correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un contextControlCode correct
     */

    $this->setContextControlCode();
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
