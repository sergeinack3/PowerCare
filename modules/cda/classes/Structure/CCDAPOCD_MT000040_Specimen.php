<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Specimen Class
 */
class CCDAPOCD_MT000040_Specimen extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_SpecimenRole
   */
  public $specimenRole;

  /**
   * Setter specimenRole
   *
   * @param CCDAPOCD_MT000040_SpecimenRole $inst CCDAPOCD_MT000040_SpecimenRole
   *
   * @return void
   */
  function setSpecimenRole(CCDAPOCD_MT000040_SpecimenRole $inst) {
    $this->specimenRole = $inst;
  }

  /**
   * Getter specimenRole
   *
   * @return CCDAPOCD_MT000040_SpecimenRole
   */
  function getSpecimenRole() {
    return $this->specimenRole;
  }

  /**
   * Assigne typeCode à SPC
   *
   * @return void
   */
  function setTypeCode() {
    $partType = new CCDAParticipationType();
    $partType->setData("SPC");
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
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"] = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["specimenRole"] = "CCDAPOCD_MT000040_SpecimenRole xml|element required";
    $props["typeCode"] = "CCDAParticipationType xml|attribute fixed|SPC";
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
     * Test avec un specimenRole correct
     */

    $specimen = new CCDAPOCD_MT000040_SpecimenRole();
    $specimen->setClassCode();
    $this->setSpecimenRole($specimen);
    $tabTest[] = $this->sample("Test avec un specimenRole correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}