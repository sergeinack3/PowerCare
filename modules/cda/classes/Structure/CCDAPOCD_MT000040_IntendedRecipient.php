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
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_InformationRecipientRole;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_IntendedRecipient Class
 */
class CCDAPOCD_MT000040_IntendedRecipient extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Person
   */
  public $informationRecipient;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $receivedOrganization;

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
   * Setter informationRecipient
   *
   * @param CCDAPOCD_MT000040_Person $inst CCDAPOCD_MT000040_Person
   *
   * @return void
   */
  function setInformationRecipient(CCDAPOCD_MT000040_Person $inst) {
    $this->informationRecipient = $inst;
  }

  /**
   * Getter informationRecipient
   *
   * @return CCDAPOCD_MT000040_Person
   */
  function getInformationRecipient() {
    return $this->informationRecipient;
  }

  /**
   * Setter receivedOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setReceivedOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->receivedOrganization = $inst;
  }

  /**
   * Getter receivedOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getReceivedOrganization() {
    return $this->receivedOrganization;
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
    $info = new CCDAx_InformationRecipientRole();
    $info->setData($inst);
    $this->classCode = $info;
  }

  /**
   * Getter classCode
   *
   * @return CCDAx_InformationRecipientRole
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
    $props["addr"]                 = "CCDAAD xml|element";
    $props["telecom"]              = "CCDATEL xml|element";
    $props["informationRecipient"] = "CCDAPOCD_MT000040_Person xml|element max|1";
    $props["receivedOrganization"] = "CCDAPOCD_MT000040_Organization xml|element max|1";
    $props["classCode"]            = "CCDAx_InformationRecipientRole xml|attribute default|ASSIGNED";
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
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec une telecom incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("HLTHCHRT");
    $tabTest[] = $this->sample("Test avec une telecom correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un informationRecipient correct
     */

    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $this->setInformationRecipient($person);
    $tabTest[] = $this->sample("Test avec une informationRecipient correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un receivedOrganization correct
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setReceivedOrganization($org);
    $tabTest[] = $this->sample("Test avec une receivedOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
