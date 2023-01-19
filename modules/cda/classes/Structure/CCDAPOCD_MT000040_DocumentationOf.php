<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAActRelationshipType;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_DocumentationOf Class
 */
class CCDAPOCD_MT000040_DocumentationOf extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_ServiceEvent
   */
  public $serviceEvent;

  /**
   * Setter serviceEvent
   *
   * @param CCDAPOCD_MT000040_ServiceEvent $inst CCDAPOCD_MT000040_ServiceEvent
   *
   * @return void
   */
  function setServiceEvent(CCDAPOCD_MT000040_ServiceEvent $inst) {
    $this->serviceEvent = $inst;
  }

  /**
   * Getter serviceEvent
   *
   * @return CCDAPOCD_MT000040_ServiceEvent
   */
  function getServiceEvent() {
    return $this->serviceEvent;
  }

  /**
   * Assigne typeCode à DOC
   *
   * @return void
   */
  function setTypeCode() {
    $actRel = new CCDAActRelationshipType();
    $actRel->setData("DOC");
    $this->typeCode = $actRel;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAActRelationshipType
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
    $props["typeId"]       = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["serviceEvent"] = "CCDAPOCD_MT000040_ServiceEvent xml|element required";
    $props["typeCode"]     = "CCDAActRelationshipType xml|attribute fixed|DOC";
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
     * Test avec un serviceEvent correct
     */

    $event = new CCDAPOCD_MT000040_ServiceEvent();
    $event->setMoodCode();
    $this->setServiceEvent($event);
    $tabTest[] = $this->sample("Test avec un serviceEvent correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode incorrect
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}