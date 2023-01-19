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
use Ox\Interop\Cda\Datatypes\Base\CCDAON;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClassOrganization;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMOrganization;

/**
 * POCD_MT000040_CustodianOrganization Class
 */
class CCDAPOCD_MT000040_CustodianOrganization extends  CCDARIMOrganization {

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
   * Setter name
   *
   * @param CCDAON $inst CCDAON
   *
   * @return void
   */
  function setName(CCDAON $inst) {
    $this->name = $inst;
  }

  /**
   * Getter name
   *
   * @return CCDAON
   */
  function getName() {
    return $this->name;
  }

  /**
   * Setter telecom
   *
   * @param CCDATEL $inst CCDATEL
   *
   * @return void
   */
  function setTelecom(CCDATEL $inst) {
    $this->telecom = $inst;
  }

  /**
   * Getter telecom
   *
   * @return CCDATEL
   */
  function getTelecom() {
    return $this->telecom;
  }

  /**
   * Setter addr
   *
   * @param CCDAAD $inst CCDAAD
   *
   * @return void
   */
  function setAddr(CCDAAD $inst) {
    $this->addr = $inst;
  }

  /**
   * Getter addr
   *
   * @return CCDAAD
   */
  function getAddr() {
    return $this->addr;
  }

  /**
   * Assigne classCode à ORG
   *
   * @return void
   */
  function setClassCode() {
    $classOrg = new CCDAEntityClassOrganization();
    $classOrg->setData("ORG");
    $this->classCode = $classOrg;
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
    $deter = new CCDAEntityDeterminer();
    $deter->setData("INSTANCE");
    $this->determinerCode = $deter;
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
    $props["typeId"]         = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]             = "CCDAII xml|element required";
    $props["name"]           = "CCDAON xml|element max|1";
    $props["telecom"]        = "CCDATEL xml|element max|1";
    $props["addr"]           = "CCDAAD xml|element max|1";
    $props["classCode"]      = "CCDAEntityClassOrganization xml|attribute fixed|ORG";
    $props["determinerCode"] = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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
     * Test avec un name incorrect
     */

    $on = new CCDAON();
    $on->setUse(array("TESTTEST"));
    $this->setName($on);
    $tabTest[] = $this->sample("Test avec un name incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name incorrect
     */

    $on->setUse(array("C"));
    $this->setName($on);
    $tabTest[] = $this->sample("Test avec un name correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom incorrect
     */

    $tel = new CCDATEL();
    $tel->setUse(array("TESTTEST"));
    $this->setTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom incorrect
     */

    $tel->setUse(array("AS"));
    $this->setTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une addr incorrect
     */

    $ad = new CCDAAD();
    $ad->setUse(array("TESTTEST"));
    $this->setAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une addr correct
     */

    $ad->setUse(array("PST"));
    $this->setAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}