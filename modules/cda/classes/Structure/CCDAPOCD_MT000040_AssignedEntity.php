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
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassAssignedEntity;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_AssignedEntity Class
 */
class CCDAPOCD_MT000040_AssignedEntity extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Person
   */
  public $assignedPerson;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $representedOrganization;

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
   * Setter assignedPerson
   *
   * @param CCDAPOCD_MT000040_Person $inst CCDAPOCD_MT000040_Person
   *
   * @return void
   */
  function setAssignedPerson(CCDAPOCD_MT000040_Person $inst) {
    $this->assignedPerson = $inst;
  }

  /**
   * Getter assignedPerson
   *
   * @return CCDAPOCD_MT000040_Person
   */
  function getAssignedPerson() {
    return $this->assignedPerson;
  }

  /**
   * Setter representedOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setRepresentedOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->representedOrganization = $inst;
  }

  /**
   * Getter representedOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getRepresentedOrganization() {
    return $this->representedOrganization;
  }

  /**
   * Assigne classCode à ASSIGNED
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClassAssignedEntity();
    $roleClass->setData("ASSIGNED");
    $this->classCode = $roleClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassAssignedEntity
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
    $props["typeId"]                  = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                      = "CCDAII xml|element min|1";
    $props["code"]                    = "CCDACE xml|element max|1";
    $props["addr"]                    = "CCDAAD xml|element";
    $props["telecom"]                 = "CCDATEL xml|element";
    $props["assignedPerson"]          = "CCDAPOCD_MT000040_Person xml|element max|1";
    $props["representedOrganization"] = "CCDAPOCD_MT000040_Organization xml|element max|1";
    $props["classCode"]               = "CCDARoleClassAssignedEntity xml|attribute fixed|ASSIGNED";
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
     * Test avec un Id incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTEST");
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id correct
     */

    $ii->setRoot("1.2.5");
    $this->resetListId();
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Code incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un Code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Code correct
     */

    $ce->setCode("TEST");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un Code correct", "Document valide");

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
     * Test avec un telecom incorrect
     */

    $tel->setUse(array("AS"));
    $this->resetListTelecom();
    $this->appendTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un assignedPerson incorrect
     */

    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $this->setAssignedPerson($person);
    $tabTest[] = $this->sample("Test avec une assignedPerson correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un representedOrganization incorrect
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setRepresentedOrganization($org);
    $tabTest[] = $this->sample("Test avec une representedOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}