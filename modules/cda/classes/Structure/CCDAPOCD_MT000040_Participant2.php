<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Participant2 Class
 */
class CCDAPOCD_MT000040_Participant2 extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_ParticipantRole
   */
  public $participantRole;

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
   * Setter awarenessCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setAwarenessCode(CCDACE $inst) {
    $this->awarenessCode = $inst;
  }

  /**
   * Getter awarenessCode
   *
   * @return CCDACE
   */
  function getAwarenessCode() {
    return $this->awarenessCode;
  }

  /**
   * Setter participantRole
   *
   * @param CCDAPOCD_MT000040_ParticipantRole $inst CCDAPOCD_MT000040_ParticipantRole
   *
   * @return void
   */
  function setParticipantRole(CCDAPOCD_MT000040_ParticipantRole $inst) {
    $this->participantRole = $inst;
  }

  /**
   * Getter participantRole
   *
   * @return CCDAPOCD_MT000040_ParticipantRole
   */
  function getParticipantRole() {
    return $this->participantRole;
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
    $part = new CCDAParticipationType();
    $part->setData($inst);
    $this->typeCode = $part;
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
    $props["time"]               = "CCDAIVL_TS xml|element max|1";
    $props["awarenessCode"]      = "CCDACE xml|element max|1";
    $props["participantRole"]    = "CCDAPOCD_MT000040_ParticipantRole xml|element required";
    $props["typeCode"]           = "CCDAParticipationType xml|attribute required";
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
     * Test avec un participantRole incorrect
     */

    $partRole = new CCDAPOCD_MT000040_ParticipantRole();
    $partRole->setTypeId();
    $this->setParticipantRole($partRole);
    $tabTest[] = $this->sample("Test avec un participantRole correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode incorrect
     */

    $this->setTypeCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec un typeCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode("CST");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un awarenessCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setAwarenessCode($ce);
    $tabTest[] = $this->sample("Test avec un contextControlCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un awarenessCode correct
     */

    $ce->setCode("TEST");
    $this->setAwarenessCode($ce);
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un contextControlCode correct
     */

    $this->setContextControlCode();
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

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

    return $tabTest;
  }
}