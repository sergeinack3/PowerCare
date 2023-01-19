<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMLivingSubject;

/**
 * POCD_MT000040_Person Class
 */
class CCDAPOCD_MT000040_Person extends CCDARIMLivingSubject {

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
    $props["typeId"]         = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["name"]           = "CCDAPN xml|element";
    $props["classCode"]      = "CCDAEntityClass xml|attribute fixed|PSN";
    $props["determinerCode"] = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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

    return $tabTest;
  }
}
