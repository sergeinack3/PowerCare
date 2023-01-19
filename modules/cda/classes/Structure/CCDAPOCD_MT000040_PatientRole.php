<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClass;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_PatientRole Class
 */
class CCDAPOCD_MT000040_PatientRole extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Patient
   */
  public $patient;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $providerOrganization;

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
   * Setter patient
   *
   * @param CCDAPOCD_MT000040_Patient $inst CCDAPOCD_MT000040_Patient
   *
   * @return void
   */
  function setPatient(CCDAPOCD_MT000040_Patient $inst) {
    $this->patient = $inst;
  }

  /**
   * Getter patient
   *
   * @return CCDAPOCD_MT000040_Patient
   */
  function getPatient() {
    return $this->patient;
  }

  /**
   * Setter providerOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setProviderOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->providerOrganization = $inst;
  }

  /**
   * Getter providerOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getProviderOrganization() {
    return $this->providerOrganization;
  }

  /**
   * Assigne classCode à PAT
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClass();
    $roleClass->setData("PAT");
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
    $props["id"]                   = "CCDAII xml|element min|1";
    $props["addr"]                 = "CCDAAD xml|element";
    $props["telecom"]              = "CCDATEL xml|element";
    $props["patient"]              = "CCDAPOCD_MT000040_Patient xml|element max|1";
    $props["providerOrganization"] = "CCDAPOCD_MT000040_Organization xml|element max|1";
    $props["classCode"]            = "CCDARoleClass xml|attribute fixed|PAT";
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
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec une classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un providerOrganization correct
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setProviderOrganization($org);
    $tabTest[] = $this->sample("Test avec une providerOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un patient correct
     */

    $pat = new CCDAPOCD_MT000040_Patient();
    $pat->setClassCode();
    $this->setPatient($pat);
    $tabTest[] = $this->sample("Test avec une patient correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}