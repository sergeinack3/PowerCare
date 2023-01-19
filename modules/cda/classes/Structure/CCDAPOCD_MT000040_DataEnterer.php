<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_DataEnterer Class
 */
class CCDAPOCD_MT000040_DataEnterer extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_AssignedEntity
   */
  public $assignedEntity;

  /**
   * Setter time
   *
   * @param CCDATS $inst CCDATS
   *
   * @return void
   */
  function setTime(CCDATS $inst) {
    $this->time = $inst;
  }

  /**
   * Getter time
   *
   * @return CCDATS
   */
  function getTime() {
    return $this->time;
  }

  /**
   * Setter assignedEntity
   *
   * @param CCDAPOCD_MT000040_AssignedEntity $inst CCDAPOCD_MT000040_AssignedEntity
   *
   * @return void
   */
  function setAssignedEntity(CCDAPOCD_MT000040_AssignedEntity $inst) {
    $this->assignedEntity = $inst;
  }

  /**
   * Getter assignedEntity
   *
   * @return CCDAPOCD_MT000040_AssignedEntity
   */
  function getAssignedEntity() {
    return $this->assignedEntity;
  }

  /**
   * Assigne typeCode à ENT
   *
   * @return void
   */
  function setTypeCode() {
    $parttype = new CCDAParticipationType();
    $parttype->setData("ENT");
    $this->typeCode = $parttype;
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
    $props["time"]               = "CCDATS xml|element max|1";
    $props["assignedEntity"]     = "CCDAPOCD_MT000040_AssignedEntity xml|element required";
    $props["typeCode"]           = "CCDAParticipationType xml|attribute fixed|ENT";
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
     * Test avec un assignedEntity correct
     */

    $assign = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assign->appendId($ii);
    $this->setAssignedEntity($assign);
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un time incorrect
     */

    $ts = new CCDATS();
    $ts->setValue("TESTTEST");
    $this->setTime($ts);
    $tabTest[] = $this->sample("Test avec un time incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un time correct
     */

    $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
    $this->setTime($ts);
    $tabTest[] = $this->sample("Test avec un time correct", "Document valide");

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