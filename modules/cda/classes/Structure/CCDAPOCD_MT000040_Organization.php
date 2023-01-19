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
use Ox\Interop\Cda\Datatypes\Base\CCDAON;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClassOrganization;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMOrganization;

/**
 * POCD_MT000040_Organization Class
 */
class CCDAPOCD_MT000040_Organization extends CCDARIMOrganization {

  /**
   * @var CCDAPOCD_MT000040_OrganizationPartOf
   */
  public $asOrganizationPartOf;

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
   * @param CCDAON $inst CCDAON
   *
   * @return void
   */
  function appendName(CCDAON $inst) {
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
   * @return CCDAON[]
   */
  function getName() {
    return $this->name;
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
   * Setter standardIndustryClassCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setStandardIndustryClassCode(CCDACE $inst) {
    $this->standardIndustryClassCode = $inst;
  }

  /**
   * Getter standardIndustryClassCode
   *
   * @return CCDACE
   */
  function getStandardIndustryClassCode() {
    return $this->standardIndustryClassCode;
  }

  /**
   * Setter asOrganizationPartOf
   *
   * @param CCDAPOCD_MT000040_OrganizationPartOf $inst CCDAPOCD_MT000040_OrganizationPartOf
   *
   * @return void
   */
  function setAsOrganizationPartOf(CCDAPOCD_MT000040_OrganizationPartOf $inst) {
    $this->asOrganizationPartOf = $inst;
  }

  /**
   * Getter asOrganizationPartOf
   *
   * @return CCDAPOCD_MT000040_OrganizationPartOf
   */
  function getAsOrganizationPartOf() {
    return $this->asOrganizationPartOf;
  }

  /**
   * Assigne classCode à ORG
   *
   * @return void
   */
  function setClassCode() {
    $entityClass = new CCDAEntityClassOrganization();
    $entityClass->setData("ORG");
    $this->classCode = $entityClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClassOrganization
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
    $props["typeId"]                    = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                        = "CCDAII xml|element";
    $props["name"]                      = "CCDAON xml|element";
    $props["telecom"]                   = "CCDATEL xml|element";
    $props["addr"]                      = "CCDAAD xml|element";
    $props["standardIndustryClassCode"] = "CCDACE xml|element max|1";
    $props["asOrganizationPartOf"]      = "CCDAPOCD_MT000040_OrganizationPartOf xml|element max|1";
    $props["classCode"]                 = "CCDAEntityClassOrganization xml|attribute fixed|ORG";
    $props["determinerCode"]            = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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
     * Test avec un addr incorrect
     */

    $ad = new CCDAAD();
    $ad->setUse(array("TESTTEST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec un addr incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un addr correct
     */

    $ad->setUse(array("PST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec un addr correct", "Document valide");

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
     * Test avec un determinerCode correct
     */

    $this->setDeterminerCode();
    $tabTest[] = $this->sample("Test avec une determinerCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un standardIndustryClassCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setStandardIndustryClassCode($ce);
    $tabTest[] = $this->sample("Test avec une standardIndustryClassCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un standardIndustryClassCode correct
     */

    $ce->setCode("TEST");
    $this->setStandardIndustryClassCode($ce);
    $tabTest[] = $this->sample("Test avec une standardIndustryClassCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name incorrect
     */

    $pn = new CCDAON();
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

    return $tabTest;
  }
}
