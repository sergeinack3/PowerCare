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
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMPerson;

/**
 * POCD_MT000040_Patient Class
 */
class CCDAPOCD_MT000040_Patient extends CCDARIMPerson {

  /**
   * @var CCDAPOCD_MT000040_Guardian
   */
  public $guardian = array();

  /**
   * @var CCDAPOCD_MT000040_Birthplace
   */
  public $birthplace;

  /**
   * @var CCDAPOCD_MT000040_LanguageCommunication
   */
  public $languageCommunication = array();

  /**
   * Setter id
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function setId(CCDAII $inst) {
    $this->id = $inst;
  }

  /**
   * Getter id
   *
   * @return CCDAII
   */
  function getId() {
    return $this->id;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPN $inst CCDAPN
   *
   * @return void
   */
  function appendName(CCDAPN $inst) {
    array_push($this->name, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListName() {
    $this->name = array();
  }

  /**
   * Getter name
   *
   * @return CCDAPN[]
   */
  function getName() {
    return $this->name;
  }

  /**
   * Setter administrativeGenderCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setAdministrativeGenderCode(CCDACE $inst) {
    $this->administrativeGenderCode = $inst;
  }

  /**
   * Getter administrativeGenderCode
   *
   * @return CCDACE
   */
  function getAdministrativeGenderCode() {
    return $this->administrativeGenderCode;
  }

  /**
   * Setter birthTime
   *
   * @param CCDATS $inst CCDATS
   *
   * @return void
   */
  function setBirthTime(CCDATS $inst) {
    $this->birthTime = $inst;
  }

  /**
   * Getter birthTime
   *
   * @return CCDATS
   */
  function getBirthTime() {
    return $this->birthTime;
  }

  /**
   * Setter maritalStatusCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setMaritalStatusCode(CCDACE $inst) {
    $this->maritalStatusCode = $inst;
  }

  /**
   * Getter maritalStatusCode
   *
   * @return CCDACE
   */
  function getMaritalStatusCode() {
    return $this->maritalStatusCode;
  }

  /**
   * Setter religiousAffiliationCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setReligiousAffiliationCode(CCDACE $inst) {
    $this->religiousAffiliationCode = $inst;
  }

  /**
   * Getter religiousAffiliationCode
   *
   * @return CCDACE
   */
  function getReligiousAffiliationCode() {
    return $this->religiousAffiliationCode;
  }

  /**
   * Setter raceCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setRaceCode(CCDACE $inst) {
    $this->raceCode = $inst;
  }

  /**
   * Getter raceCode
   *
   * @return CCDACE
   */
  function getRaceCode() {
    return $this->raceCode;
  }

  /**
   * Setter ethnicGroupCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setEthnicGroupCode(CCDACE $inst) {
    $this->ethnicGroupCode = $inst;
  }

  /**
   * Getter ethnicGroupCode
   *
   * @return CCDACE
   */
  function getEthnicGroupCode() {
    return $this->ethnicGroupCode;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Guardian $inst CCDAPOCD_MT000040_Guardian
   *
   * @return void
   */
  function appendGuardian(CCDAPOCD_MT000040_Guardian $inst) {
    array_push($this->guardian, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListGuardian() {
    $this->guardian = array();
  }

  /**
   * Getter guardian
   *
   * @return CCDAPOCD_MT000040_Guardian[]
   */
  function getGuardian() {
    return $this->guardian;
  }

  /**
   * Setter birthplace
   *
   * @param CCDAPOCD_MT000040_Birthplace $inst CCDAPOCD_MT000040_Birthplace
   *
   * @return void
   */
  function setBirthplace(CCDAPOCD_MT000040_Birthplace $inst) {
    $this->birthplace = $inst;
  }

  /**
   * Getter birthplace
   *
   * @return CCDAPOCD_MT000040_Birthplace
   */
  function getBirthplace() {
    return $this->birthplace;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_LanguageCommunication $inst CCDAPOCD_MT000040_LanguageCommunication
   *
   * @return void
   */
  function appendLanguageCommunication(CCDAPOCD_MT000040_LanguageCommunication $inst) {
    array_push($this->languageCommunication, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListLanguageCommunication() {
    $this->languageCommunication = array();
  }

  /**
   * Getter languageCommunication
   *
   * @return CCDAPOCD_MT000040_LanguageCommunication[]
   */
  function getLanguageCommunication() {
    return $this->languageCommunication;
  }

  /**
   * Assigne classCode à PSN
   *
   * @return void
   */
  function setClassCode() {
    $entityClass = new CCDAEntityClass();
    $entityClass->setData("PSN");
    $this->classCode = $entityClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClass
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne determinerCode à INSTANCE
   *
   * @return void
   */
  function setDeterminerCode() {
    $determiner = new CCDAEntityDeterminer();
    $determiner->setData("INSTANCE");
    $this->determinerCode = $determiner;
  }

  /**
   * Getter determinerCode
   *
   * @return CCDAEntityDeterminer
   */
  function getDeterminerCode() {
    return $this->determinerCode;
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
    $props["name"]                     = "CCDAPN xml|element";
    $props["administrativeGenderCode"] = "CCDACE xml|element max|1";
    $props["birthTime"]                = "CCDATS xml|element max|1";
    $props["maritalStatusCode"]        = "CCDACE xml|element max|1";
    $props["religiousAffiliationCode"] = "CCDACE xml|element max|1";
    $props["raceCode"]                 = "CCDACE xml|element max|1";
    $props["ethnicGroupCode"]          = "CCDACE xml|element max|1";
    $props["guardian"]                 = "CCDAPOCD_MT000040_Guardian xml|element";
    $props["birthplace"]               = "CCDAPOCD_MT000040_Birthplace xml|element max|1";
    $props["languageCommunication"]    = "CCDAPOCD_MT000040_LanguageCommunication xml|element";
    $props["classCode"]                = "CCDAEntityClass xml|attribute fixed|PSN";
    $props["determinerCode"]           = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = array();

    /**
     * Test avec les valeurs null
     */

    $tabTest[] = $this->sample("Test avec les valeurs null", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeId correct
     */

    $this->setTypeId();
    $tabTest[] = $this->sample("Test avec un typeId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un determinerCode correct
     */

    $this->setDeterminerCode();
    $tabTest[] = $this->sample("Test avec un determinerCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.1.1");
    $this->resetListTemplateId();
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->setId($ii);
    $tabTest[] = $this->sample("Test avec un Id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.9");
    $this->setId($ii);
    $tabTest[] = $this->sample("Test avec un Id correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name incorrect
     */

    $pn = new CCDAPN();
    $pn->setUse(array("TESTTEST"));
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name correct
     */

    $pn->setUse(array("C"));
    $this->resetListName();
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un administrativeGenderCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setAdministrativeGenderCode($ce);
    $tabTest[] = $this->sample("Test avec un administrativeGenderCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un administrativeGenderCode correct
     */

    $ce->setCode("TEST");
    $this->setAdministrativeGenderCode($ce);
    $tabTest[] = $this->sample("Test avec un administrativeGenderCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un birthTime incorrect
     */

    $ts = new CCDATS();
    $ts->setValue("TESTTEST");
    $this->setBirthTime($ts);
    $tabTest[] = $this->sample("Test avec un birthTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un birthTime correct
     */

    $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
    $this->setBirthTime($ts);
    $tabTest[] = $this->sample("Test avec un birthTime correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un maritalStatusCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setMaritalStatusCode($ce);
    $tabTest[] = $this->sample("Test avec un maritalStatusCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un maritalStatusCode correct
     */

    $ce->setCode("TEST");
    $this->setMaritalStatusCode($ce);
    $tabTest[] = $this->sample("Test avec un maritalStatusCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un religiousAffiliationCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setReligiousAffiliationCode($ce);
    $tabTest[] = $this->sample("Test avec un religiousAffiliationCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un religiousAffiliationCode correct
     */

    $ce->setCode("TEST");
    $this->setReligiousAffiliationCode($ce);
    $tabTest[] = $this->sample("Test avec un religiousAffiliationCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un raceCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setRaceCode($ce);
    $tabTest[] = $this->sample("Test avec un raceCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un raceCode correct
     */

    $ce->setCode("TEST");
    $this->setRaceCode($ce);
    $tabTest[] = $this->sample("Test avec un raceCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un ethnicGroupCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setEthnicGroupCode($ce);
    $tabTest[] = $this->sample("Test avec un ethnicGroupCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un ethnicGroupCode correct
     */

    $ce->setCode("TEST");
    $this->setEthnicGroupCode($ce);
    $tabTest[] = $this->sample("Test avec un ethnicGroupCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un birthplace correct
     */

    $birthPlace = new CCDAPOCD_MT000040_Birthplace();
    $pla = new CCDAPOCD_MT000040_Place();
    $pla->setClassCode();
    $birthPlace->setPlace($pla);
    $this->setBirthplace($birthPlace);
    $tabTest[] = $this->sample("Test avec un birthplace correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un languageCommunication correct
     */

    $language = new CCDAPOCD_MT000040_LanguageCommunication();
    $language->setTypeId();
    $this->appendLanguageCommunication($language);
    $tabTest[] = $this->sample("Test avec un languageCommunication correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un guardian correct
     */

    $guard = new CCDAPOCD_MT000040_Guardian();
    $per = new CCDAPOCD_MT000040_Person();
    $per->setClassCode();
    $guard->setGuardianPerson($per);
    $this->appendGuardian($guard);
    $tabTest[] = $this->sample("Test avec un guardian correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
