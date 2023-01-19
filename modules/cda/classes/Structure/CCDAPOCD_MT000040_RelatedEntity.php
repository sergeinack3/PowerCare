<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassMutualRelationship;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_RelatedEntity Class
 */
class CCDAPOCD_MT000040_RelatedEntity extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Person
   */
  public $relatedPerson;

  /**
   * Setter code
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setCode(CCDACE $inst) {
    $this->code = $inst;
  }

  /**
   * Getter code
   *
   * @return CCDACE
   */
  function getCode() {
    return $this->code;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAAD $inst CCDAAD
   *
   * @return void
   */
  function appendAddr(CCDAAD $inst) {
    array_push($this->addr, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListAddr() {
    $this->addr = array();
  }

  /**
   * Getter addr
   *
   * @return CCDAAD[]
   */
  function getAddr() {
    return $this->addr;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDATEL $inst CCDATEL
   *
   * @return void
   */
  function appendTelecom(CCDATEL $inst) {
    array_push($this->telecom, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListTelecom() {
    $this->telecom = array();
  }

  /**
   * Getter telecom
   *
   * @return CCDATEL[]
   */
  function getTelecom() {
    return $this->telecom;
  }

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
   * Setter relatedPerson
   *
   * @param CCDAPOCD_MT000040_Person $inst CCDAPOCD_MT000040_Person
   *
   * @return void
   */
  function setRelatedPerson(CCDAPOCD_MT000040_Person $inst) {
    $this->relatedPerson = $inst;
  }

  /**
   * Getter relatedPerson
   *
   * @return CCDAPOCD_MT000040_Person
   */
  function getRelatedPerson() {
    return $this->relatedPerson;
  }

  /**
   * Setter classCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setClassCode($inst) {
    if (!$inst) {
      $this->classCode = null;
      return;
    }
    $role = new CCDARoleClassMutualRelationship();
    $role->setData($inst);
    $this->classCode = $role;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassMutualRelationship
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
    $props["typeId"]        = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]          = "CCDACE xml|element max|1";
    $props["addr"]          = "CCDAAD xml|element";
    $props["telecom"]       = "CCDATEL xml|element";
    $props["effectiveTime"] = "CCDAIVL_TS xml|element max|1";
    $props["relatedPerson"] = "CCDAPOCD_MT000040_Person xml|element max|1";
    $props["classCode"]     = "CCDARoleClassMutualRelationship xml|attribute required";
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
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec un classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("PRS");
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code correct
     */

    $ce->setCode("SYNTH");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une addr incorrect
     */

    $ad = new CCDAAD();
    $ad->setUse(array("TESTTEST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une addr correct
     */

    $ad->setUse(array("PST"));
    $this->resetListAddr();
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom incorrect
     */

    $tel = new CCDATEL();
    $tel->setUse(array("TESTTEST"));
    $this->appendTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom correct
     */

    $tel->setUse(array("AS"));
    $this->resetListTelecom();
    $this->appendTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom correct", "Document valide");

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

    /**
     * Test avec un relatedPerson correct
     */

    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $this->setRelatedPerson($person);
    $tabTest[] = $this->sample("Test avec un relatedPerson correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}