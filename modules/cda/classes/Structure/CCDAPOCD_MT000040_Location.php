<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationTargetLocation;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Location Class
 */
class CCDAPOCD_MT000040_Location extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_HealthCareFacility
   */
  public $healthCareFacility;

  /**
   * Setter healthCareFacility
   *
   * @param CCDAPOCD_MT000040_HealthCareFacility $inst CCDAPOCD_MT000040_HealthCareFacility
   *
   * @return void
   */
  function setHealthCareFacility(CCDAPOCD_MT000040_HealthCareFacility $inst) {
    $this->healthCareFacility = $inst;
  }

  /**
   * Getter healthCareFacility
   *
   * @return CCDAPOCD_MT000040_HealthCareFacility
   */
  function getHealthCareFacility() {
    return $this->healthCareFacility;
  }

  /**
   * Assigne typeCode à LOC
   *
   * @return void
   */
  function setTypeCode() {
    $particpTar = new CCDAParticipationTargetLocation();
    $particpTar->setData("LOC");
    $this->typeCode = $particpTar;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAParticipationTargetLocation
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
    $props["typeId"]             = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["healthCareFacility"] = "CCDAPOCD_MT000040_HealthCareFacility xml|element required";
    $props["typeCode"]           = "CCDAParticipationTargetLocation xml|attribute fixed|LOC";
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
     * Test avec un healthCareFacility incorrect
     */

    $healt = new CCDAPOCD_MT000040_HealthCareFacility();
    $healt->setTypeId();
    $this->setHealthCareFacility($healt);
    $tabTest[] = $this->sample("Test avec un healthCareFacility correct", "Document valide");

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