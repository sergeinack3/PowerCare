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
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClass;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_Guardian Class
 */
class CCDAPOCD_MT000040_Guardian extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Person
   */
  public $guardianPerson;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $guardianOrganization;

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
   * Setter guardianPerson
   *
   * @param CCDAPOCD_MT000040_Person $inst CCDAPOCD_MT000040_Person
   *
   * @return void
   */
  function setGuardianPerson(CCDAPOCD_MT000040_Person $inst) {
    $this->guardianPerson = $inst;
  }

  /**
   * Getter guardianPerson
   *
   * @return CCDAPOCD_MT000040_Person
   */
  function getGuardianPerson() {
    return $this->guardianPerson;
  }

  /**
   * Setter guardianOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setGuardianOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->guardianOrganization = $inst;
  }

  /**
   * Getter guardianOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getGuardianOrganization() {
    return $this->guardianOrganization;
  }

  /**
   * Assigne classCode à GUARD
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClass();
    $roleClass->setData("GUARD");
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
    $props["typeId"]               = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                   = "CCDAII xml|element";
    $props["code"]                 = "CCDACE xml|element max|1";
    $props["addr"]                 = "CCDAAD xml|element";
    $props["telecom"]              = "CCDATEL xml|element";
    $props["guardianPerson"]       = "CCDAPOCD_MT000040_Person xml|element required";
    $props["guardianOrganization"] = "CCDAPOCD_MT000040_Organization xml|element required";
    $props["classCode"]            = "CCDARoleClass xml|attribute fixed|GUARD";
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
     * Test avec un guardianPerson correct
     */

    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $this->setGuardianPerson($person);
    $tabTest[] = $this->sample("Test avec un guardianPerson correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un guardianOrganization correct
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setGuardianOrganization($org);
    $tabTest[] = $this->sample("Test avec un guardianOrganization correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un guardianOrganization correct
     */

    $this->guardianPerson = null;
    $tabTest[] = $this->sample("Test avec un guardianOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

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

    return $tabTest;
  }
}