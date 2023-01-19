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
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMPatientEncounter;

/**
 * POCD_MT000040_EncompassingEncounter Class
 */
class CCDAPOCD_MT000040_EncompassingEncounter extends CCDARIMPatientEncounter {

  /**
   * @var CCDAPOCD_MT000040_ResponsibleParty
   */
  public $responsibleParty;

  /**
   * @var CCDAPOCD_MT000040_EncounterParticipant
   */
  public $encounterParticipant = array();

  /**
   * @var CCDAPOCD_MT000040_Location
   */
  public $location;

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function appendId(CCDAII $inst) {
    array_push($this->id, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListId() {
    $this->id = array();
  }

  /**
   * Getter id
   *
   * @return CCDAII[]
   */
  function getId() {
    return $this->id;
  }

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
   * Setter dischargeDispositionCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setDischargeDispositionCode(CCDACE $inst) {
    $this->dischargeDispositionCode = $inst;
  }

  /**
   * Getter dischargeDispositionCode
   *
   * @return CCDACE
   */
  function getDischargeDispositionCode() {
    return $this->dischargeDispositionCode;
  }

  /**
   * Setter responsibleParty
   *
   * @param CCDAPOCD_MT000040_ResponsibleParty $inst CCDAPOCD_MT000040_ResponsibleParty
   *
   * @return void
   */
  function setResponsibleParty(CCDAPOCD_MT000040_ResponsibleParty $inst) {
    $this->responsibleParty = $inst;
  }

  /**
   * Getter responsibleParty
   *
   * @return CCDAPOCD_MT000040_ResponsibleParty
   */
  function getResponsibleParty() {
    return $this->responsibleParty;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_EncounterParticipant $inst CCDAPOCD_MT000040_EncounterParticipant
   *
   * @return void
   */
  function appendEncounterParticipant(CCDAPOCD_MT000040_EncounterParticipant $inst) {
    array_push($this->encounterParticipant, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListEncounterParticipant() {
    $this->encounterParticipant = array();
  }

  /**
   * Getter encounterParticipant
   *
   * @return CCDAPOCD_MT000040_EncounterParticipant[]
   */
  function getEncounterParticipant() {
    return $this->encounterParticipant;
  }

  /**
   * Setter location
   *
   * @param CCDAPOCD_MT000040_Location $inst CCDAPOCD_MT000040_Location
   *
   * @return void
   */
  function setLocation(CCDAPOCD_MT000040_Location $inst) {
    $this->location = $inst;
  }

  /**
   * Getter location
   *
   * @return CCDAPOCD_MT000040_Location
   */
  function getLocation() {
    return $this->location;
  }

  /**
   * Setter classCode
   *
   * @return void
   */
  function setClassCode() {
    $classAct = new CCDAActClass();
    $classAct->setData("ENC");
    $this->classCode = $classAct;
  }

  /**
   * Getter classCode
   *
   * @return CCDAActClass
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne moodCode à EVN
   *
   * @return void
   */
  function setMoodCode() {
    $mood = new CCDAActMood();
    $mood->setData("EVN");
    $this->moodCode = $mood;
  }

  /**
   * Getter moodCode
   *
   * @return CCDAActMood
   */
  function getMoodCode() {
    return $this->moodCode;
  }

  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]                   = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                       = "CCDAII xml|element max|1";
    $props["code"]                     = "CCDACE xml|element max|1";
    $props["effectiveTime"]            = "CCDAIVL_TS xml|element required";
    $props["dischargeDispositionCode"] = "CCDACE xml|element max|1";
    $props["responsibleParty"]         = "CCDAPOCD_MT000040_ResponsibleParty xml|element max|1";
    $props["encounterParticipant"]     = "CCDAPOCD_MT000040_EncounterParticipant xml|element";
    $props["location"]                 = "CCDAPOCD_MT000040_Location xml|element max|1";
    $props["classCode"]                = "CCDAActClass xml|attribute fixed|ENC";
    $props["moodCode"]                 = "CCDAActMood xml|attribute fixed|EVN";
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
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode();
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.9");
    $this->resetListId();
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id correct", "Document valide");

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
     * Test avec un dischargeDispositionCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un dischargeDispositionCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un dischargeDispositionCode correct
     */

    $ce->setCode("TEST");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un dischargeDispositionCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un responsibleParty correct
     */

    $responsible = new CCDAPOCD_MT000040_ResponsibleParty();
    $assignedEntity = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assignedEntity->appendId($ii);
    $responsible->setAssignedEntity($assignedEntity);
    $this->setResponsibleParty($responsible);
    $tabTest[] = $this->sample("Test avec un responsibleParty correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un encounterParticipant correct
     */

    $encounter = new CCDAPOCD_MT000040_EncounterParticipant();
    $assignedEntity = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assignedEntity->appendId($ii);
    $encounter->setAssignedEntity($assignedEntity);
    $encounter->setTypeCode("ADM");
    $this->appendEncounterParticipant($encounter);
    $tabTest[] = $this->sample("Test avec un encounterParticipant correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un location correct
     */
    $loc = new CCDAPOCD_MT000040_Location();
    $healt = new CCDAPOCD_MT000040_HealthCareFacility();
    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $healt->setServiceProviderOrganization($org);
    $loc->setHealthCareFacility($healt);
    $this->setLocation($loc);
    $tabTest[] = $this->sample("Test avec un location correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}