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
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_ServiceEventPerformer;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Performer1 Class
 */
class CCDAPOCD_MT000040_Performer1 extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_AssignedEntity
   */
  public $assignedEntity;

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
   * @param CCDAIVL_TS $inst CCDAIVL_TS
   *
   * @return void
   */
  function setTime(CCDAIVL_TS $inst) {
    $this->time = $inst;
  }

  /**
   * Getter time
   *
   * @return CCDAIVL_TS
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
   * Setter typeCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setTypeCode($inst) {
    if (!$inst) {
      $this->typeCode = null;
      return;
    }
    $ser = new CCDAx_ServiceEventPerformer();
    $ser->setData($inst);
    $this->typeCode = $ser;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAx_ServiceEventPerformer
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
    $props["typeId"]         = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["functionCode"]   = "CCDACE xml|element max|1";
    $props["time"]           = "CCDAIVL_TS xml|element max|1";
    $props["assignedEntity"] = "CCDAPOCD_MT000040_AssignedEntity xml|element required";
    $props["typeCode"]       = "CCDAx_ServiceEventPerformer xml|attribute required";
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
     * Test avec un typeCode incorrect
     */

    $this->setTypeCode("TEST");
    $tabTest[] = $this->sample("Test avec un typeCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode("PRF");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un assignedEntity correct
     */

    $assign = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.25.5");
    $assign->appendId($ii);
    $this->setAssignedEntity($assign);
    $tabTest[] = $this->sample("Test avec un typeCode incorrect", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un effectiveTime incorrect
     */

    $ivl_ts = new CCDAIVL_TS();
    $hi = new CCDAIVXB_TS();
    $hi->setValue("TESTTEST");
    $ivl_ts->setHigh($hi);
    $this->setTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un effectiveTime correct
     */

    $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $ivl_ts->setHigh($hi);
    $this->setTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime correct", "Document valide");

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
     * Test avec un functionCode correct
     */

    $ce->setCode("TESTTEST");
    $this->setFunctionCode($ce);
    $tabTest[] = $this->sample("Test avec un functionCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}