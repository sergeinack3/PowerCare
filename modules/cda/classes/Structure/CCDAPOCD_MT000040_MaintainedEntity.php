<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClass;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_MaintainedEntity Class
 */
class CCDAPOCD_MT000040_MaintainedEntity extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Person
   */
  public $maintainingPerson;

  /**
   * Setter effectiveTime
   *
   * @param CCDAIVL_TS $inst CCDAIVL_TS
   *
   * @return void
   */
  function setEffectiveTime(CCDAIVL_TS $inst) {
    $this->effectiveTime = $inst;
  }

  /**
   * Getter effectiveTime
   *
   * @return CCDAIVL_TS
   */
  function getEffectiveTime() {
    return $this->effectiveTime;
  }

  /**
   * Setter maintainingPerson
   *
   * @param CCDAPOCD_MT000040_Person $inst CCDAPOCD_MT000040_Person
   *
   * @return void
   */
  function setMaintainingPerson(CCDAPOCD_MT000040_Person $inst) {
    $this->maintainingPerson = $inst;
  }

  /**
   * Getter maintainingPerson
   *
   * @return CCDAPOCD_MT000040_Person
   */
  function getMaintainingPerson() {
    return $this->maintainingPerson;
  }

  /**
   * Assigne classCode à MNT
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClass();
    $roleClass->setData("MNT");
    $this->classCode = $roleClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClass
   */
  function getClassCode() {
    return $this->classCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]            = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["effectiveTime"]     = "CCDAIVL_TS xml|element max|1";
    $props["maintainingPerson"] = "CCDAPOCD_MT000040_Person xml|element required";
    $props["classCode"]         = "CCDARoleClass xml|attribute fixed|MNT";
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
     * Test avec un maintainingPerson correct
     */

    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $this->setMaintainingPerson($person);
    $tabTest[] = $this->sample("Test avec un maintainingPerson correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un effectiveTime incorrect
     */

    $ivl_ts = new CCDAIVL_TS();
    $hi = new CCDAIVXB_TS();
    $hi->setValue("TESTTEST");
    $ivl_ts->setHigh($hi);
    $this->setEffectiveTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un effectiveTime correct
     */

    $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $ivl_ts->setHigh($hi);
    $this->setEffectiveTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}