<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Author Class
 */
class CCDAPOCD_MT000040_Author extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_AssignedAuthor
   */
  public $assignedAuthor;

  /**
   * Setter functionCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setFunctionCode(CCDACE $inst) {
    $this->functionCode = $inst;
  }

  /**
   * Getter functionCode
   *
   * @return CCDACE
   */
  function getFunctionCode() {
    return $this->functionCode;
  }

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
   * Setter assignedAuthor
   *
   * @param CCDAPOCD_MT000040_AssignedAuthor $inst CCDAPOCD_MT000040_AssignedAuthor
   *
   * @return void
   */
  function setAssignedAuthor(CCDAPOCD_MT000040_AssignedAuthor $inst) {
    $this->assignedAuthor = $inst;
  }

  /**
   * Getter assignedAuthor
   *
   * @return CCDAPOCD_MT000040_AssignedAuthor
   */
  function getAssignedAuthor() {
    return $this->assignedAuthor;
  }

  /**
   * Assigne typeCode à AUT
   *
   * @return void
   */
  function setTypeCode() {
    $particip = new CCDAParticipationType();
    $particip->setData("AUT");
    $this->typeCode = $particip;
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
    $control = new CCDAContextControl();
    $control->setData("OP");
    $this->contextControlCode = $control;
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
    $props["functionCode"]       = "CCDACE xml|element max|1";
    $props["time"]               = "CCDATS xml|element required";
    $props["assignedAuthor"]     = "CCDAPOCD_MT000040_AssignedAuthor xml|element required";
    $props["typeCode"]           = "CCDAParticipationType xml|attribute fixed|AUT";
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
    $tabTest[] = $this->sample("Test avec un time correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un assignedAuthor correct
     */

    $assigned = new CCDAPOCD_MT000040_AssignedAuthor();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assigned->appendId($ii);
    $this->setAssignedAuthor($assigned);
    $tabTest[] = $this->sample("Test avec un assignedAuthor correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un functionCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setFunctionCode($ce);
    $tabTest[] = $this->sample("Test avec un functionCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un functionCode incorrect
     */

    $ce->setCode("TEST");
    $this->setFunctionCode($ce);
    $tabTest[] = $this->sample("Test avec un functionCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode incorrect
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un contextControlCode incorrect
     */

    $this->setContextControlCode();
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}